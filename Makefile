all:	checkconfig updatecode assets updatedb updateusers 

checkconfig:
ifeq "$(wildcard config.php)" ""
	@echo =
	@echo =	You must create config.php file first
	@echo =	Start by copying config.sample.php
	@echo =
	@exit 1
endif

updatecode:
ifneq "$(wildcard .git )" ""
	git pull origin master
	git submodule init
	git submodule update
endif

updateusers:
	cd users && $(MAKE)

db:	updatedb
updatedb:
	php dbupgrade.php

rel:	release
release: assets releasetag packages

releasetag:
ifndef v
	# Must specify version as 'v' param
	#
	#   make rel v=1.1.1
	#
else
ifneq "$(shell git ls-files -m asset_versions.tsv)" ""
	# first if asset fingerprints were changed, we need to commit them into the repository
	git commit asset_versions.tsv -m "Checking in asset fingerprints for v${v}"
endif

	#
	# Tagging it with release tag
	#
	git tag -a REL_${subst .,_,${v}}
	git push --tags
endif

packages:
ifndef v
	# Must specify version as 'v' param
	#
	#   make rel v=1.1.1
	#
else
	# generate the package
	git clone . showslow_${v}
	cd showslow_${v} && git checkout REL_${subst .,_,${v}}
	cd showslow_${v} && ${MAKE} updatecode
	cd showslow_${v}/users && ${MAKE} updatecode
	cd showslow_${v} && ${MAKE} assets 
	cd showslow_${v} && find ./ -name "\.git*" | xargs -n10 rm -r

	tar -c showslow_${v} |bzip2 > showslow_${v}.tar.bz2
	zip -r showslow_${v}.zip showslow_${v}
	rm -rf showslow_${v}
endif

# No need for this really since we patched Timeplot clone on Github
timeplot-patch:
	patch -p0 <timeplot.patch

# from svn-assets project
clean: noassets

assets:
	# generating crc32 hashes of all assets that should be versioned
	find ./ -type f | grep -v -E '^./(blog|tools|timeline|timeplot|ajax|users|svn-assets|flot\/examples)/' | grep -E '\.(png|jpg|js|gif|ico)$$' | sort | xargs -n10 php crc32.php | sed -e 's/\t\.\//\t/' > asset_versions.tsv
	# uncomment next line when we'll have any CSS files to process
	find ./ -name '*.css' -not -wholename "./timeplot/*" -not -wholename "./timeline/*" -not -wholename "./ajax/*" -not -wholename "./users/*" -not -wholename "./blog/*" -not -wholename "./tools/*" | xargs -n1 php svn-assets/cssurlrewrite.php

noassets:
	rm asset_versions.tsv
	find ./ -name '*_deploy.css' | xargs -n10 rm -f
