all:	update updatedb assets
	cd users && $(MAKE)

update:
	if [ -d .svn ]; then svn update; fi

updatedb:
	php dbupgrade.php

cleantables:
	sed -e 's/Database: showslow.*/Database: showslow/' -e 's/ AUTO_INCREMENT=[0-9]*\b//' -i tables.sql

rel:	release
release: assets
ifndef v
	# Must specify version as 'v' param
	#
	#   make rel v=1.1.1
	#
else
	#
	# Tagging it with release tag
	#
	svn copy . https://showslow.googlecode.com/svn/tags/REL_${subst .,_,${v}}/
	#
	# Creating release tarball and zip
	#
	svn co http://showslow.googlecode.com/svn/tags/REL_${subst .,_,${v}}/ showslow_${v}
	(cd showslow_${v}/users && $(MAKE) .git)
	find showslow_${v} -type d -name .svn |xargs -n10 rm -rf
	cp asset_versions.php showslow_${v}/asset_versions.php

	# Not including Makefile into the package since it's not doing anything but release packaging
	tar -c showslow_${v} |gzip > showslow_${v}.tgz
	zip -r showslow_${v}.zip showslow_${v}
	rm -rf showslow_${v}
	# upload to Google Code repository (need account with enough permissions)
	googlecode/googlecode_upload.py -s "ShowSlow v${v} (zip)" -p showslow -l "Featured,Type-Archive,OpSys-All" showslow_${v}.zip
	googlecode/googlecode_upload.py -s "ShowSlow v${v} (tarball)" -p showslow -l "Featured,Type-Archive,OpSys-All" showslow_${v}.tgz
	rm showslow_${v}.tgz showslow_${v}.zip
endif

timeplot-patch:
	patch -p0 <timeplot.patch

# from svn-assets project
clean: noassets

assets:
	if [ -d .svn ]; then svn status --verbose --xml |php svn-assets/svnassets.php > asset_versions.php; fi

# uncomment next line when we'll have any CSS files to process
#find ./ -name '*.css' -not -wholename "./timeplot/*" -not -wholename "./timeline/*" -not -wholename "./ajax/*" -not -wholename "./users/*" | xargs -n1 php svn-assets/cssurlrewrite.php

noassets:
	cp svn-assets/no-assets.php asset_versions.php
	find ./ -name '*_deploy.css' | xargs -n10 rm -f
