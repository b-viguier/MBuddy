# https://hub.docker.com/r/instrumentisto/geckodriver/dockerfile#!
FROM debian:buster-slim

ARG firefox_ver=122.0.1
ARG geckodriver_ver=0.34.0

RUN apt-get update \
 && apt-get upgrade -y \
 && apt-get install -y --no-install-recommends --no-install-suggests \
            ca-certificates \
 && update-ca-certificates \
    \
 # Install tools for building
 && toolDeps=" \
        curl bzip2 \
    " \
 && apt-get install -y --no-install-recommends --no-install-suggests \
            $toolDeps \
    \
 # Install Firefox
 && apt-get install -y --no-install-recommends --no-install-suggests \
            firefox-esr \
 # Download and install geckodriver
 && curl -fL -o /tmp/geckodriver.tar.gz \
         https://github.com/mozilla/geckodriver/releases/download/v${geckodriver_ver}/geckodriver-v${geckodriver_ver}-linux64.tar.gz \
 && tar -xzf /tmp/geckodriver.tar.gz -C /tmp/ \
 && chmod +x /tmp/geckodriver \
 && mv /tmp/geckodriver /usr/local/bin/ \
    \
 # Cleanup unnecessary stuff
 && apt-get purge -y --auto-remove \
                  -o APT::AutoRemove::RecommendsImportant=false \
            $toolDeps \
 && rm -rf /var/lib/apt/lists/* \
           /tmp/*

# As this image cannot run in non-headless mode anyway, it's better to forcibly
# enable it, regardless whether WebDriver client requests it in capabilities or
# not.
ENV MOZ_HEADLESS=1

EXPOSE 4444

ENTRYPOINT ["geckodriver"]

CMD ["--log=debug", "--host=webdriver"]
