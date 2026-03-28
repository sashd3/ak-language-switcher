app_name=ak_language_switcher
version=$(shell sed -n 's/.*<version>\(.*\)<\/version>.*/\1/p' appinfo/info.xml)
build_dir=build
tarball=$(build_dir)/$(app_name)-$(version).tar.gz
sign_dir=$(build_dir)/sign

.PHONY: all appstore clean

all: appstore

clean:
	rm -rf $(build_dir)

appstore: clean
	mkdir -p $(sign_dir)/$(app_name)
	# App metadata
	cp -r appinfo $(sign_dir)/$(app_name)/
	# PHP backend
	cp -r lib $(sign_dir)/$(app_name)/
	cp -r vendor $(sign_dir)/$(app_name)/
	# Frontend (built JS, no source maps)
	mkdir -p $(sign_dir)/$(app_name)/js
	cp js/*.js $(sign_dir)/$(app_name)/js/
	# Translations
	cp -r l10n $(sign_dir)/$(app_name)/
	# Images
	cp -r img $(sign_dir)/$(app_name)/
	# Templates
	cp -r templates $(sign_dir)/$(app_name)/
	# Root files
	cp LICENSE $(sign_dir)/$(app_name)/
	cp CHANGELOG.md $(sign_dir)/$(app_name)/
	# Composer autoload
	cp composer.json $(sign_dir)/$(app_name)/
	cp composer.lock $(sign_dir)/$(app_name)/
	# Remove macOS resource fork files that cause ReflectionException on Linux servers
	find $(sign_dir) -name '._*' -delete
	# Create tarball (COPYFILE_DISABLE prevents macOS from adding ._* files)
	COPYFILE_DISABLE=1 tar czf $(tarball) -C $(sign_dir) $(app_name)
	# Cleanup staging
	rm -rf $(sign_dir)
	@echo ""
	@echo "Tarball created: $(tarball)"
	@echo "Size: $$(du -h $(tarball) | cut -f1)"
