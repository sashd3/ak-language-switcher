# Contributing to Language Switcher

Thanks for your interest in contributing!

## Getting Started

1. Fork the repository
2. Clone your fork
3. Install dependencies:
   ```bash
   npm install
   composer install
   ```
4. Create a feature branch from `main`:
   ```bash
   git checkout -b feat/your-feature main
   ```

## Development

```bash
# Build with watch mode
npm run dev

# Production build
npm run build
```

Copy or symlink the app folder into your Nextcloud `custom_apps/` directory to test. Requires a running Nextcloud instance.

## Branch Naming

- `feat/description` — new features
- `fix/description` — bug fixes
- `docs/description` — documentation changes

If there's a related issue, include the number: `feat/42-dark-mode`

## Pull Requests

1. Create your branch from `main`
2. Make sure `npm run build` succeeds
3. Test on both authenticated and public share pages
4. Open a PR against `main`
5. Fill in the PR template

## Reporting Bugs

Use the [Bug Report](https://github.com/sashd3/ak-language-switcher/issues/new?template=bug_report.md) template.

## Suggesting Features

Use the [Feature Request](https://github.com/sashd3/ak-language-switcher/issues/new?template=feature_request.md) template.

## License

By contributing, you agree that your contributions will be licensed under the [AGPL-3.0-or-later](LICENSE).
