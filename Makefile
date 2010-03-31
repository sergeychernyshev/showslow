all:
	svn update
	php dbupgrade.php

rel:	release
release:
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
	svn export https://showslow.googlecode.com/svn/tags/REL_${subst .,_,${v}}/ showslow_${v}
	# Not including Makefile into the package since it's not doing anything but release packaging
	tar -c showslow_${v} |gzip > showslow_${v}.tgz
	zip -r showslow_${v}.zip showslow_${v}
	rm -rf showslow_${v}
	# upload to Google Code repository (need account with enough permissions)
	googlecode/googlecode_upload.py -s "ShowSlow v${v} (tarball)" -p showslow -l "Featured,Type-Archive,OpSys-All" showslow_${v}.tgz
	googlecode/googlecode_upload.py -s "ShowSlow v${v} (zip)" -p showslow -l "Featured,Type-Archive,OpSys-All" showslow_${v}.zip
	rm showslow_${v}.tgz showslow_${v}.zip
endif

timeplot-patch:
	patch -p0 <timeplot.patch
