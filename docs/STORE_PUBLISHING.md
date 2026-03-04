# NC App Store Publishing Guide

## Prerequisites

- Nextcloud App Store account: https://apps.nextcloud.com
- GPG key (for signing releases)
- App certificate (RSA-4096, obtained from Nextcloud)

## App Store Requirements

### info.xml Pflichtfelder
- `<id>` — App-ID, muss mit Ordnername übereinstimmen
- `<name>` — Anzeigename
- `<summary>` — Kurzbeschreibung (max 128 Zeichen)
- `<description>` — Ausführliche Beschreibung (CDATA erlaubt)
- `<version>` — Semver (z.B. `1.0.0`)
- `<licence>` — SPDX-Identifier (z.B. `AGPL-3.0-or-later`), Pflicht ab NC 31+
- `<author>` — Name des Autors
- `<namespace>` — PHP Namespace
- `<category>` — z.B. `customization`, `tools`, `security`
- `<dependencies><nextcloud min-version="" max-version=""/></dependencies>`

### Optionale aber empfohlene Felder
- `<bugs>` — Issue-Tracker URL
- `<repository>` — Git-Repository URL
- `<website>` — Projekt-Website
- `<screenshot>` — Screenshot-URL(s) für den Store

### Tarball-Format
- `.tar.gz` Archiv
- Top-Level-Ordner = App-ID (z.B. `ak_language_switcher/`)
- Muss `appinfo/info.xml` enthalten
- Keine Entwicklungsdateien (node_modules, src, .git, etc.)
- Keine Source-Maps in Produktion

## Release-Prozess (Schritt für Schritt)

### 1. Version vorbereiten
```bash
# Version in info.xml und package.json aktualisieren
# CHANGELOG.md aktualisieren
# Commit erstellen
git add -A
git commit -m "Release v1.0.0"
git tag v1.0.0
```

### 2. Tarball erstellen
```bash
make appstore
# Erzeugt: build/ak_language_switcher-<version>.tar.gz
```

### 3. Tarball verifizieren
```bash
tar tzf build/ak_language_switcher-*.tar.gz | head -20
# Prüfen: Top-Folder ist ak_language_switcher/
# Prüfen: appinfo/info.xml vorhanden
# Prüfen: Keine verbotenen Dateien
```

### 4. Tarball signieren (siehe Signing-Prozess)
```bash
openssl dgst -sha512 -sign ~/.nextcloud/certificates/ak_language_switcher.key \
  build/ak_language_switcher-*.tar.gz | openssl base64 > build/signature.base64
```

### 5. Im App Store hochladen
- https://apps.nextcloud.com/developer/apps/upload
- Tarball-URL oder Datei hochladen
- Signatur einfügen

### 6. Git Release erstellen
```bash
git push origin main --tags
# GitHub Release mit Tarball als Asset erstellen
gh release create v1.0.0 build/ak_language_switcher-*.tar.gz \
  --title "v1.0.0" --notes "See CHANGELOG.md"
```

## App-Signierung (Certificate Signing)

### Warum?
Nextcloud prüft die Integrität von Apps via Code-Signierung. Ohne Signatur erscheint eine Warnung im Admin-Panel.

### Schritt 1: RSA-4096 Schlüssel erstellen
```bash
mkdir -p ~/.nextcloud/certificates
openssl genrsa -out ~/.nextcloud/certificates/ak_language_switcher.key 4096
```

### Schritt 2: CSR (Certificate Signing Request) erstellen
```bash
openssl req -new \
  -key ~/.nextcloud/certificates/ak_language_switcher.key \
  -out ~/.nextcloud/certificates/ak_language_switcher.csr \
  -subj "/CN=ak_language_switcher"
```

### Schritt 3: CSR bei Nextcloud einreichen
1. Fork: https://github.com/nextcloud/app-certificate-requests
2. CSR-Datei kopieren nach `requests/ak_language_switcher.csr`
3. Pull Request erstellen
4. Warten auf Review und Merge
5. Zertifikat wird in `certificates/ak_language_switcher.crt` bereitgestellt

### Schritt 4: Zertifikat herunterladen
```bash
# Nach Merge des PRs:
curl -o ~/.nextcloud/certificates/ak_language_switcher.crt \
  https://raw.githubusercontent.com/nextcloud/app-certificate-requests/master/certificates/ak_language_switcher.crt
```

### Schritt 5: Release signieren
```bash
# Tarball signieren
openssl dgst -sha512 -sign ~/.nextcloud/certificates/ak_language_switcher.key \
  build/ak_language_switcher-1.0.0.tar.gz | openssl base64 > build/signature.base64

# Signatur verifizieren
openssl dgst -sha512 -verify <(openssl x509 -in ~/.nextcloud/certificates/ak_language_switcher.crt -pubkey -noout) \
  -signature <(base64 -d build/signature.base64) \
  build/ak_language_switcher-1.0.0.tar.gz
```

## Wichtige Hinweise

- **Private Key** (`ak_language_switcher.key`) NIEMALS committen oder teilen!
- `.key` und `.csr` Dateien gehören in `.gitignore`
- Jede neue Version braucht einen neuen signierten Tarball
- Der App Store prüft, dass die Version in `info.xml` höher ist als die vorherige
- `max-version` in `info.xml` bestimmt NC-Kompatibilität — regelmässig aktualisieren

## Referenzen

- App Store Doku: https://nextcloudappstore.readthedocs.io/en/latest/
- info.xml Schema: https://apps.nextcloud.com/schema/apps/info.xsd
- Certificate Requests: https://github.com/nextcloud/app-certificate-requests
- App Development Guide: https://docs.nextcloud.com/server/latest/developer_manual/app_publishing_maintenance/
