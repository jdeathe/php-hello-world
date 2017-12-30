FROM jdeathe/centos-ssh-apache-php:2.2.2

ARG PACKAGE_NAME="app"
ARG PACKAGE_PATH="/opt/${PACKAGE_NAME}"

RUN rpm --rebuilddb \
	&& yum -y install \
		--setopt=tsflags=nodocs \
		--disableplugin=fastestmirror \
		php56u-pecl-xdebug \
	&& sed -i \
		-e 's~^; .*$~~' \
		-e 's~^;*$~~' \
		-e '/^$/d' \
		-e 's~^\[~\n\[~g' \
		/etc/php.d/15-xdebug.ini \
	&& rm -rf /var/cache/yum/* \
	&& yum clean all

COPY bin \
	${PACKAGE_PATH}/bin/
COPY etc \
	${PACKAGE_PATH}/etc/
COPY etc/php.d/51-php.ini.develop \
	${PACKAGE_PATH}/etc/php.d/51-php.ini
COPY public_html \
	${PACKAGE_PATH}/public_html/
COPY src \
	${PACKAGE_PATH}/src/

RUN sed \
		-e 's~^;\(opcache.enable_cli=\).*$~\11~g' \
		-e 's~^\(opcache.max_accelerated_files=\).*$~\132531~g' \
		-e 's~^;\(opcache.validate_timestamps=\).*$~\11~g' \
		/etc/php.d/10-opcache.ini.default \
		> /etc/php.d/10-opcache.ini \
	&& sed -ri \
		-e 's~^;?(session.save_handler = ).*$~\1"${PHP_OPTIONS_SESSION_SAVE_HANDLER:-files}"~' \
		-e 's~^;?(session.save_path = ).*$~\1"${PHP_OPTIONS_SESSION_SAVE_PATH:-/var/lib/php/session}"~' \
		${PACKAGE_PATH}/etc/php.d/50-php.ini \
	&& chown -R app:app-www ${PACKAGE_PATH} \
	&& find ${PACKAGE_PATH} -type d -exec chmod 750 {} + \
	&& find ${PACKAGE_PATH}/var -type d -exec chmod 770 {} + \
	&& find ${PACKAGE_PATH} -type f -exec chmod 640 {} + \
	&& find ${PACKAGE_PATH}/bin -type f -exec chmod 750 {} +

EXPOSE 22 80 443

ENV APACHE_EXTENDED_STATUS_ENABLED=true \
	APACHE_OPERATING_MODE="development" \
	SSH_AUTOSTART_SSHD=true \
	SSH_AUTOSTART_SSHD_BOOTSTRAP=true
