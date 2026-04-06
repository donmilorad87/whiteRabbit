FROM phpmyadmin:latest

COPY config.user.inc.php /etc/phpmyadmin/config.user.inc.php
COPY dark-theme.css /var/www/html/dark-theme.css

# Inject dark theme CSS into phpMyAdmin header template
RUN HEADER_FILE=$(find /var/www/html/templates -name "header.twig" -o -name "base.twig" 2>/dev/null | head -1) \
    && if [ -n "$HEADER_FILE" ]; then \
        sed -i 's|</head>|<link rel="stylesheet" type="text/css" href="/pma/dark-theme.css">\n</head>|' "$HEADER_FILE"; \
    fi \
    # Fallback: also inject via PHP auto_prepend to cover all pages
    && echo 'php_value auto_prepend_file "/var/www/html/dark-inject.php"' >> /var/www/html/.htaccess

# Create a PHP injector as fallback for CSS
RUN echo '<?php if(!defined("DARK_INJECTED")){define("DARK_INJECTED",true);ob_start(function($b){return str_replace("</head>","<link rel=\"stylesheet\" href=\"dark-theme.css\">\n</head>",$b);});}' > /var/www/html/dark-inject.php

EXPOSE 80
