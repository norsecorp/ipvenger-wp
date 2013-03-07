=== IPVenger ===
Contributors: norse-corp,sburkett,tomsti,jbelich
Donate link: http://www.ipvenger.com
Tags: security,block,IP,ipviking,dangerous,protection
Requires at least: 3.4
Tested up to: 3.5.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

IPVenger protects your WordPress installation by blocking access by IPs associated with high-risk behavior.

== Description ==

IPVenger uses NorseCorp IPViking live threat assessment technology to evaluate each IP requesting access to your WordPress site.

When a request comes in, the IP address is sent to the IPViking server, which assigns a numerical risk factor based on live data.

IPs whose risk factor exceeds a user-configurable threshold are blocked before they can harm your site. 

Detailed analytics are provided, as is the ability to blacklist specific IP addresses and countries of origin. 

== Installation ==

1.     Login to your WordPress site
2.     Select “Plugins” from the admin menu
3.     Select “Add New” from the admin menu
4.     Search for 'IPVenger'
5.     Click “Install”
6.     You must activate the plugin by following the following steps:
7.     Select "IPVenger" from the admin menu
8.     Click "General Settings" admin menu option (NOTE: If you see “Activation Error” this means you will need FTP access to manually edit your wp-config.ph file.  Follow the instructions shown on the error page, then reload the page and continue.)
9.     Still under IPVenger "General Setting" enter your product license key and select “Activate”. Activation will take a few seconds. If you do not have a license key, visit www.ipvenger.com to obtain one.
10.     Select the type of site that best describes the main function of your website
11.     Select who you want appeal notifications to go to, under ‘Appeal Settings’


== Frequently asked questions ==

= Why is the number of IPs being blocked from my site so high?  =

The majority of these requests originate from Botnets which blindly crawl the Internet looking for vulnerabilities to exploit, and can repeatedly attack the same site from different IP addresses. In cases where a site has relatively light traffic, the number of attacks can easily exceed the number of legitimate visitors.

= I received an Appeal Request email. What do I do? =

If you received an Appeal Request email, it means that a visitor to your website was initially blocked and they are requesting access to your website.

The vast majority of web traffic that is blocked by IPVenger will be automated bots and malicious traffic that won't bother to try to get past this form. However, sometimes legitimate visitors may be blocked. This could happen because they are behind a proxy or because they have an unknown virus. 

When this happens, you have the ability to grant the visitor the ability to reach your website. That's where the appeals process comes in.

When a website visitor is blocked due to a high IP score, they will be presented with a page that displays a message and a form to submit to request access to your website.  Through the IP Control Center, you can grant the visitor permission by selecting the "allow" option in White/Blacklist column dropdown for their IP address.

= Will this service affect my site's performance?  =

When a visitor comes to your site for the first time, the IPQ score and other data about their IP is retrieved from the IPViking server which takes microseconds. The wait time is subject to typical network latency. Once the user has established a session, the overhead is negligible.

= Why does the number of visitors shown differ from my Google Analytics?  =

IPVenger and Google Analytics count traffic in fundamentally different ways. Google Analytics attempts to count unique, human visitors to your site. IPVenger counts visits from automated agents, users with scripting disabled, and legitimate human users. Together, IPVenger and Google Analytics provide you with a complete picture of your site traffic.

= Will this plugin influence my Google analytics?  =

If a user is blocked, they will not be recorded as a visitor by Google Analytics. In practice this will have an extremely minor impact on your data as the vast majority of requests blocked by IPVenger come from Botnets and other automated systems. These automated requests do not appear in Google Analytics because they do not execute the javascript that updates the analytical counter.

= Will this plugin affect my SEO?  =

Only IPs with a history of risky behavior are blocked so "good" bots and crawlers will have access to your page and will see exactly the same content they would without IPVenger protection. Traffic from some high-risk content aggregators may be blocked, but current search engines reduce the page rank when they detect inbound links from these "black hat" sites, so the net effect is likely to be positive.

= How do I Get a Product Key? =

Just go to ipvenger.com and sign up for an account.

== Screenshots ==

1. Advanced Settings
2. Analytics
3. Blocked Traffic by Type
4. Control Center
5. Country Blacklist
6. Email Settings
7. General Settings
8. Latest IPs Blocked
9. Message Settings 
10. Threats by Country

== Changelog ==

= 0.1 =
* Beta release.

= 1.0.0 =
* Initial release

= 1.0.1 =
* allow=true for 417 return from API to allow internal RFC1918 IP's

= 1.0.2 =
* add free trial API Key limited to 100 API Calls
* add admin error alerts for key errors