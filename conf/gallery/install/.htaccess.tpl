RewriteEngine on

Options -Indexes
AddDefaultCharset UTF-8
# Apache mimetype configuration
AddType text/cache-manifest .manifest

#php_value display_errors 1
#php_value register_globals 0
#php_value safe_mode 0
#php_value request_order "EGPCS"
#php_value magic_quotes_gpc 0
#php_value magic_quotes_runtime 0
#php_value magic_quotes_sybase 0
#php_value max_execution_time 1000
#php_value max_input_time 1000
#php_value post_max_size 100M
#php_value upload_max_filesize 100M

SetEnvIf Host "blueocarina.local" FF_TOP_DIR=C:\xampp\htdocs\blueocarina
SetEnvIf Host "blueocarina.local" FF_PROJECT_DIR=
SetEnvIf Host "blueocarina.local" DISK_PATH=C:\xampp\htdocs\blueocarina
SetEnvIf Host "blueocarina.local" SITE_PATH=


ErrorDocument 404 /error/notfound
ErrorDocument 403 /error/forbidden

RewriteCond %{HTTP_HOST} ^blueocarina\.net$
RewriteRule (.*)$ http://www.blueocarina.net/$1 [R=301,L,QSA]

RewriteCond   %{HTTP_HOST}      ^static\.blueocarina\.net$
RewriteCond   %{REQUEST_URI}  	!^/cm/showfiles\.php
RewriteRule   ^(.*)    /cm/showfiles\.php/$0 [L,QSA]

RewriteCond   %{HTTP_HOST}  	^dev\.blueocarina\.net$ [OR]
RewriteCond   %{HTTP_HOST}  	^www\.blueocarina\.net$
RewriteCond   %{REQUEST_URI}  	^/asset
RewriteRule   ^asset/(.*)    /cache/$1 [L]

RewriteCond   %{HTTP_HOST}  	^dev\.blueocarina\.net$ [OR]
RewriteCond   %{HTTP_HOST}  	^www\.blueocarina\.net$
RewriteCond   %{REQUEST_URI}  	^/media 
RewriteRule   ^media/(.*)    /cache/_thumb/$1 [L]

RewriteCond   %{HTTP_HOST}  	^dev\.blueocarina\.net$ [OR]
RewriteCond   %{HTTP_HOST}  	^www\.blueocarina\.net$
RewriteCond   %{REQUEST_URI}	^/modules
RewriteCond   %{REQUEST_URI}	!^/modules/([^/]+)/themes(.+)
RewriteRule  ^modules/([^/]+)(.+)  /modules/$1/themes$2 [L,QSA]

RewriteCond   %{HTTP_HOST}  	^dev\.blueocarina\.net$ [OR]
RewriteCond   %{HTTP_HOST}  	^www\.blueocarina\.net$
RewriteCond   %{REQUEST_URI}  	!^/index\.php
RewriteCond   %{REQUEST_URI}  	!^/cm/main\.php
RewriteCond   %{REQUEST_URI}  	!^/cm/showfiles\.php
RewriteCond   %{REQUEST_URI}  	!^/themes
RewriteCond   %{REQUEST_URI}  	!^/applets/.*/?themes
RewriteCond   %{REQUEST_URI}  	!^/modules/.*/?themes
RewriteCond   %{REQUEST_URI}    !^/uploads
RewriteCond   %{REQUEST_URI}    !^/cache
RewriteCond   %{REQUEST_URI}    !^/asset
RewriteCond   %{REQUEST_URI}    !^/media
RewriteCond   %{REQUEST_URI}  	!^/robots\.txt
RewriteCond   %{REQUEST_URI}  	!^/favicon
RewriteCond   %{REQUEST_URI}  	!^/conf/gallery/install
RewriteCond   %{REQUEST_URI}  	!^/conf/gallery/updater
RewriteCond   %{REQUEST_URI}  	!^/router\.php
RewriteRule   ^(.*)    /index\.php/$0 [L,QSA]

<IfModule mod_deflate.c>
# force deflate for mangled headers 
# developer.yahoo.com/blogs/ydn/posts/2010/12/pushing-beyond-gzipping/
    <IfModule mod_setenvif.c>
      <IfModule mod_headers.c>
        SetEnvIfNoCase ^(Accept-EncodXng|X-cept-Encoding|X{15}|~{15}|-{15})$ ^((gzip|deflate)\s*,?\s*)+|[X~-]{4,13}$ HAVE_Accept-Encoding
        RequestHeader append Accept-Encoding "gzip,deflate" env=HAVE_Accept-Encoding
      </IfModule>
    </IfModule>

  # Legacy versions of Apache
  AddOutputFilterByType DEFLATE text/html text/plain text/css application/json
  AddOutputFilterByType DEFLATE application/javascript
  AddOutputFilterByType DEFLATE text/xml application/xml text/x-component
  AddOutputFilterByType DEFLATE application/xhtml+xml application/rss+xml application/atom+xml
  AddOutputFilterByType DEFLATE image/svg+xml application/vnd.ms-fontobject application/x-font-ttf font/opentype image/x-icon
</IfModule>


<IfModule mod_expires.c>
  ExpiresActive on

# Perhaps better to whitelist expires rules? Perhaps.
  ExpiresDefault                          "access plus 1 month"

# cache.appcache needs re-requests in FF 3.6 (thx Remy ~Introducing HTML5)
  ExpiresByType text/cache-manifest       "access plus 0 seconds"



# Your document html
  ExpiresByType text/html                 "access plus 1 week"

# Data
  ExpiresByType text/xml                  "access plus 0 seconds"
  ExpiresByType application/xml           "access plus 0 seconds"
  ExpiresByType application/json          "access plus 0 seconds"

# RSS feed
  ExpiresByType application/rss+xml       "access plus 1 hour"

# Favicon (cannot be renamed)
  ExpiresByType image/x-icon              "access plus 1 month" 

# Media: images, video, audio
  ExpiresByType image/gif                 "access plus 1 month"
  ExpiresByType image/png                 "access plus 1 month"
  ExpiresByType image/jpg                 "access plus 1 month"
  ExpiresByType image/jpeg                "access plus 1 month"
  ExpiresByType video/ogg                 "access plus 1 month"
  ExpiresByType audio/ogg                 "access plus 1 month"
  ExpiresByType video/mp4                 "access plus 1 month"
  ExpiresByType video/webm                "access plus 1 month"

# HTC files  (css3pie)
  ExpiresByType text/x-component          "access plus 1 month"

# Webfonts
  ExpiresByType font/truetype             "access plus 1 month"
  ExpiresByType font/opentype             "access plus 1 month"
  ExpiresByType application/x-font-woff   "access plus 1 month"
  ExpiresByType image/svg+xml             "access plus 1 month"
  ExpiresByType application/vnd.ms-fontobject "access plus 1 month"

# CSS and JavaScript
  ExpiresByType text/css                  "access plus 1 year"
  ExpiresByType application/javascript    "access plus 1 year"
  ExpiresByType text/javascript           "access plus 1 year"

</IfModule>

<IfModule mod_headers.c>
    <FilesMatch "\.(js|css|xml|gz|svg)$"> 
        Header set Cache-Control: public
    </FilesMatch>
	# FileETag None is not enough for every server.
	#  Header unset ETag

	Header always append X-Frame-Options SAMEORIGIN

  <FilesMatch "\.(html|js|css|xml|gz|svg)$">
      Header append Vary: Accept-Encoding
  </FilesMatch>     
</IfModule>

# Since we`re sending far-future expires, we dont need ETags for static content.
# developer.yahoo.com/performance/rules.html#etags
FileETag None
