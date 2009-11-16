all:

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
	svn export . showslow_${subst .,_,${v}}
	# Not including Makefile into the package since it's not doing anything but release packaging
	rm showslow_${subst .,_,${v}}/Makefile
	tar -c showslow_${subst .,_,${v}} |gzip > showslow_${v}.tgz
	zip -r showslow_${v}.zip showslow_${subst .,_,${v}}
	rm -rf showslow_${subst .,_,${v}}
endif

timeplot-patch:
	patch -p0 <timeplot.patch
