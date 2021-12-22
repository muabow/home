# ANSI Color Code
COLOR_RED	= \033[0;31m
COLOR_GREEN	= \033[0;32m
COLOR_BLUE	= \033[0;34m
COLOR_RESET	= \033[0m


UID			:= $(shell id -u)
GID			:= $(shell id -g)

VERSION   		:= $(shell head -1 version)
BUILD_DATE       	:= $(shell date +%Y%m%d)
BUILD_TIME       	:= $(shell date +%H%M%S)
LATEST_BUILD_INFO	 = latestBuildInfo






TARGET=ethernetManager.sh
PACKAGE	= ethernetManager


.PHONY: all check_user show-info ethernetManager.sh dist distclean clean

all: show-info
	@$(MAKE) $(TARGET)



check_user:
	@(	\
		if [ $(UID) -ne 0 ] && [ $(UID) -ne 1000 ] ; then	\
			echo "$(COLOR_RED)"	;\
			echo "UID and GID must be root(0:0) or 1000:1000"	;\
			echo "Please use 'sudo' command."	;\
			echo "$(COLOR_RESET)"	;\
			echo	;\
			exit 1	;\
		fi	;\
	)
	@echo



show-info:
	@echo -n "$(COLOR_BLUE)"
	@echo -n "$(COLOR_RESET)"

	@echo



$(TARGET):
	@echo
	# Create target

	@echo $(VERSION) > $(LATEST_BUILD_INFO)
	@echo $(BUILD_DATE) >> $(LATEST_BUILD_INFO)
	@echo $(BUILD_TIME) >> $(LATEST_BUILD_INFO)
	
	@chown `echo $${SUDO_UID}`:`echo $${SUDO_GID}` $(TARGET)	;\

	# Create target: Done
	@echo

	@echo "$(COLOR_GREEN)$(TARGET) : Succeeded$(COLOR_RESET)"
	@echo



dist:
	@$(MAKE) check_user

	# Make Package
	
	@echo -n "$(COLOR_BLUE)"
	@echo Version : `sed -n '1p' $(LATEST_BUILD_INFO)`
	@echo Build Date : `sed -n '2p' $(LATEST_BUILD_INFO)`
	@echo Build Time : `sed -n '3p' $(LATEST_BUILD_INFO)`
	@echo Target : $(TARGET)
	@echo -n "$(COLOR_RESET)"
	@echo


	@install -o1000 -g1000 -m755 -d ./dpkg/opt/interm/bin/
	@install -o1000 -g1000 -m755 $(TARGET) ./dpkg/opt/interm/bin/
	@install -o1000 -g1000 -m755 S04_ethernetManager.sh ./dpkg/opt/interm/bin/
	@chown 1000:1000 -R ./dpkg/opt

	@(\
		latest_version=`sed -n '1p' $(LATEST_BUILD_INFO)`		&&\
		latest_build_date=`sed -n '2p' $(LATEST_BUILD_INFO)`		&&\
		latest_build_time=`sed -n '3p' $(LATEST_BUILD_INFO)`		&&\
		package_file=$(PACKAGE)_"$${latest_version}"_armhf.deb		&&\
		sed -i 's/Version:.*/Version: '$${latest_version}'+'$${latest_build_date}'-'$${latest_build_time}'/g' ./dpkg/DEBIAN/control		&&\
		dpkg -b ./dpkg $${package_file}		&&\
		echo '$(COLOR_GREEN)'$${package_file} : Succeeded'$(COLOR_RESET)'	&&\
		echo &&\
		dpkg -I $${package_file}		;\
		chown `echo $${SUDO_UID}`:`echo $${SUDO_GID}` $${package_file}	;\
	)

	# Make Package: Done
	@echo



distclean: clean
	@$(MAKE) check_user

	# Clean distribution files

	rm -Rf ./dpkg/opt
	rm -Rf $(PACKAGE)_*.deb

	# Clean distribution files: Done
	@echo



clean:
	# Clean execution files
	rm -Rf $(LATEST_BUILD_INFO)

	# Clean execution files: Done
	@echo



