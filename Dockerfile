ARG IMAGE="jdeathe/centos-ssh-apache-php:1.13.2"

FROM "${IMAGE}"

ARG PACKAGE_NAME="app"
ARG PACKAGE_PATH="/opt/${PACKAGE_NAME}"

# Source image's default Apache DocumetRoot public directory is public_html.
# Set to public for this project. Package name and path are based on build
# arguments - recreate their environment variables too.
ENV \
	APACHE_CONTENT_ROOT="/var/www/${PACKAGE_NAME}" \
	APACHE_PUBLIC_DIRECTORY="public" \
	PACKAGE_PATH="/opt/${PACKAGE_NAME}"

SHELL ["/bin/bash", "-c"]

RUN if rpm -q --quiet php72u-common; \
	then \
		yum -y install \
			--setopt=tsflags=nodocs \
			--disableplugin=fastestmirror \
			php72u-pecl-xdebug \
		&& sed -i \
			-e 's~^; .*$~~' \
			-e 's~^;*$~~' \
			-e '/^$/d' \
			-e 's~^\[~\n\[~g' \
			/etc/php.d/15-xdebug.ini \
		&& sed -i \
			-e 's~^;\(opcache.enable_cli=\).*$~\11~g' \
			-e 's~^\(opcache.max_accelerated_files=\).*$~\132531~g' \
			-e 's~^;\(opcache.validate_timestamps=\).*$~\11~g' \
			/etc/php.d/10-opcache.ini; \
	else \
		yum -y install \
			--setopt=tsflags=nodocs \
			--disableplugin=fastestmirror \
			php-pecl-xdebug \
		&& sed -i \
			-e 's~^; .*$~~' \
			-e 's~^;*$~~' \
			-e '/^$/d' \
			-e 's~^\[~\n\[~g' \
			/etc/php.d/xdebug.ini; \
	fi \
	&& rm -rf /var/cache/yum/* \
	&& yum clean all \
	&& rm -rf /opt/app \
	&& rm -rf ${PACKAGE_PATH} \
	&& mkdir -p ${PACKAGE_PATH}/var/{log,session,tmp}

COPY etc \
	${PACKAGE_PATH}/etc/
COPY etc/php.d/51-php.ini.develop \
	${PACKAGE_PATH}/etc/php.d/51-php.ini
COPY public \
	${PACKAGE_PATH}/public/
COPY src \
	${PACKAGE_PATH}/src/

RUN if [[ -f /usr/share/php-pecl-apc/apc.php ]]; then \
		sed -i \
			-e "s~php_uname('n');~gethostname();~g" \
			/usr/share/php-pecl-apc/apc.php; \
		cp \
			/usr/share/php-pecl-apc/apc.php \
			${PACKAGE_PATH}/public/_apc.php; \
	fi \
	&& chown -R \
		${APACHE_SYSTEM_USER}:${APACHE_RUN_GROUP} \
		${PACKAGE_PATH} \
	&& find ${PACKAGE_PATH} -type d -exec chmod 750 {} + \
	&& find ${PACKAGE_PATH}/var -type d -exec chmod 770 {} + \
	&& find ${PACKAGE_PATH} -type f -exec chmod 640 {} +
