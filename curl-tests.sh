#!/bin/bash
curl  --insecure --referer https://www.googl.de/q=maxi --user-agent  "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15" https://contao52.contao5dev/kontakt.html | grep "visitor_count invisible"
sleep 6
curl  --insecure --referer https://www.google.de/q=maximus --user-agent  "Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/W.X.Y.Z Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)" https://contao52.contao5dev/kontakt.html | grep "visitor_count invisible"
sleep 6
curl  --insecure --referer https://www.google.de/q=maximus --user-agent  "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15" https://contao52.contao5dev/kontakt.html | grep "visitor_count invisible"
sleep 6

curl  --insecure --referer https://contao.ninja/ --user-agent  "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15" "https://contao52.contao5dev/visitors/scco?vcid=1&scrw=1024&scrh=1024&scriw=1023&scrih=1023"

curl  --insecure  --user-agent  "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.16" https://contao52.contao5dev/news-details/bienenhonig.html | grep "visitor_count invisible"

curl  --insecure  --user-agent  "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.16" https://contao52.contao5dev/news-details/honigbienen-leben-in-einem-grossen-volk.html | grep "visitor_count invisible"

curl  --insecure  --user-agent  "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.16" https://contao52.contao5dev/faq-leser/was-ist-der-unterschied-zwischen-honigbienen-und-wildbienen.html | grep "visitor_count invisible"

curl  --insecure  --user-agent  "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.16" https://contao52.contao5dev/events-details/vortrag-zum-thema-honig-und-wildbienen-2.html | grep "visitor_count invisible"

curl  --insecure  --user-agent  "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.16" https://contao52.contao5dev/news-details.html | grep "visitor_count invisible"