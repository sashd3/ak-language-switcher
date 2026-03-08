# Release Workflow — ak_language_switcher

## 1. Zertifikat (einmalig)

### Status
- PR: https://github.com/nextcloud/app-certificate-requests/pull/936
- RSA Key: `~/.nextcloud/certificates/ak_language_switcher.key`
- CSR eingereicht: `ak_language_switcher/ak_language_switcher.csr`

### Wenn PR gemergt wird

1. Zertifikat herunterladen:
```bash
curl -o ~/.nextcloud/certificates/ak_language_switcher.crt \
  https://raw.githubusercontent.com/nextcloud/app-certificate-requests/master/ak_language_switcher/ak_language_switcher.crt
```

2. Prüfen ob es korrekt ist:
```bash
openssl x509 -in ~/.nextcloud/certificates/ak_language_switcher.crt -noout -subject
# Sollte zeigen: CN = ak_language_switcher
```

---

## 2. Erster Release (v1.0.0)

### Tarball bauen
```bash
rm -rf js/ && npm run build
make appstore
# → build/ak_language_switcher-1.0.0.tar.gz
```

### Tarball signieren
```bash
openssl dgst -sha512 \
  -sign ~/.nextcloud/certificates/ak_language_switcher.key \
  build/ak_language_switcher-1.0.0.tar.gz | openssl base64 > build/signature.base64
```

### Signatur verifizieren
```bash
openssl dgst -sha512 \
  -verify <(openssl x509 -in ~/.nextcloud/certificates/ak_language_switcher.crt -pubkey -noout) \
  -signature <(base64 -d build/signature.base64) \
  build/ak_language_switcher-1.0.0.tar.gz
# Muss "Verified OK" ausgeben
```

### Git Tag + GitHub Release
```bash
git tag v1.0.0
git push origin main --tags

gh release create v1.0.0 build/ak_language_switcher-1.0.0.tar.gz \
  --title "v1.0.0 — Initial Release" \
  --notes "See [CHANGELOG.md](CHANGELOG.md) for details."
```

### Im App Store einreichen
1. https://apps.nextcloud.com/developer/apps/upload
2. Download-URL: die URL des Tarballs vom GitHub Release
   (Rechtsklick auf den Tarball im Release → Link kopieren)
3. Signatur: Inhalt von `build/signature.base64` einfügen
4. Absenden

---

## 3. Zukünftige Releases (z.B. v1.1.0)

### Code ändern
```bash
git checkout -b feat/mein-feature
# ... Code anpassen ...
git add ...
git commit -s -m "Beschreibung"
git push origin feat/mein-feature
# PR erstellen → reviewen → merge → Branch löschen
```

### Version bumpen
In `appinfo/info.xml`:
```xml
<version>1.1.0</version>
```

In `CHANGELOG.md` neuen Eintrag hinzufügen.

### Bauen + Signieren + Release
```bash
# Bauen
rm -rf js/ && npm run build
make appstore

# Signieren
openssl dgst -sha512 \
  -sign ~/.nextcloud/certificates/ak_language_switcher.key \
  build/ak_language_switcher-1.1.0.tar.gz | openssl base64 > build/signature.base64

# Tag + Release
git tag v1.1.0
git push origin main --tags

gh release create v1.1.0 build/ak_language_switcher-1.1.0.tar.gz \
  --title "v1.1.0" \
  --notes "See [CHANGELOG.md](CHANGELOG.md) for details."
```

### Im App Store aktualisieren
1. https://apps.nextcloud.com/developer/apps/upload
2. Neue Tarball-URL + neue Signatur einfügen
3. Store prüft: Version in info.xml > vorherige Version
4. Update wird veröffentlicht → alle NC-Instanzen sehen es

---

## 4. Dev-Server (unabhängig vom Store)

Für lokales Testen auf deinem Server:
```bash
./deploy.sh
```

Das deployed direkt per rsync nach `custom_apps/ak_language_switcher` auf deinem Server. Hat nichts mit dem Store zu tun.

---

## Wichtige Dateien

| Datei | Ort | Zweck |
|---|---|---|
| Private Key | `~/.nextcloud/certificates/ak_language_switcher.key` | Signierung (GEHEIM!) |
| Zertifikat | `~/.nextcloud/certificates/ak_language_switcher.crt` | Verifizierung |
| GPG Key | `460DFF666A0D581228F22643F4DA789E8674EE95` | Git Commit Signierung |

**NIEMALS committen:** `.key` Dateien, SSH Keys, GPG Private Keys
