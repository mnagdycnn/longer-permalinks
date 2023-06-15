### Longer Permalinks

Contributors: antithesisgr, Mohamed Nagdy

Author link: http://www.antithesis.gr

Tags: permalinks, long, slugs, slugs length, long title, post_name size, titles, non-latin, url, permalinks limitation, long url, long slug

Requires at least: 4.0

Tested up to: 6.1

Stable tag: 1.30

License: GPLv2 or later

License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allow long permalinks in your WordPress. Useful especially for using non-latin characters in permalinks. 
Respects future relevant core updates.

**Description**

This plugin allows you to use longer URLs (permalinks), by allowing much more characters in your titles and slug. The important is that this is done in a way that is future compatible with WordPress core updates.
There is a 200 characters limitation on WordPress core nowadays and this limit is raised to 3000 with the plugin.
Plugin is always extending the current WordPress core code - your long URLs will work even after WordPress core updates.
This plugin is really useful especially in non-latin slugs because of the required url escaping that increases the length of your permalinks a lot.


**Features**
- Upon activation the available slug length (post title) will become 3000, allowing long permalinks.
- Required functionality is automatically applied without changing WordPress core files.
- Even after core updates, plugin will automatically apply required changes and just keep your long URLs working.
- Plugin requires at least MySQL 5.0.3

**Installation**

1. Upload plugin folder to the `/wp-content/plugins/longer-permalinks/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. That's it!

**Changelog**

**1.30**
- Make the backup file in written in uploads directory inside wp-content

**1.28**
- Refactoring and minor improvements

**1.27**
- Refactoring and minor improvements

**1.26**
- Refactoring code

**1.25**
- Bugfix: correct handling of autosaves and revisions on WP core updates

**1.24**
- Bug fixes for WordPress Multi-site compatibility

**1.23**
- Tested on WP 5.5.1 

**1.22**
- Bugfix: lock name should not exceed 64 chars for some MySQL flavors/versions
- Tested on WP 5.4

**1.21**
- Important bugfixes.

**1.20 & 1.19**
- Important changes to handle extra load on database upgrades and first installation.
We now use explicit db locks to handle concurrent requests on those situations gracefully.
(not tested on active database clusters like Galera Cluster or any STATEMENT based replication setups)

**1.18**
- Tested on 5.3.2

**1.17**
- Fix for WordPress upgrade process that needs a separate step for the database upgrade - "Database Update Required" cases (credits: @margroup for investigating the problem and testing the solution)
- Proper handling on WordPress upgrades to avoid unnecessary database locks
- Source code refactoring
- Uninstall actions update

**1.16**
- Minor regex adjustment

**1.15**
- IMPORTANT: Please update plugin to latest version BEFORE upgrading your WordPress to 5.x if you want to avoid truncated long permalinks.
- Plugin Major update: long permalinks suvrive the WordPress 5.0 upgrade.

**1.14**
- Provide more thorough admin output on edge cases
- Icon added
- FAQ updated
- Minor speed improvement
- Tested on 4.9.1

**1.13**
- Bug fix, fclose was not needed.

**1.12**
- Bug fix, $wpdb was not global (credits: @takisbig).

**1.11**
- Minor changes

**1.1**
- Tested on 4.7.3

**1.0**
- Initial release


**Frequently Asked Questions**

= What can I do with this plugin? =
You can enjoy a lot more characters in your slugs and permalinks, without the default core limitation and have future core compatibility at the same time.
= What do you mean by "future core compatibility"? =
The plugin is using PHP reflection to dynamically apply the required changes in WordPress core every time you update your WordPress. It automatically detects any WordPress updates and reapplies the required changes anytime this is needed.

**Screenshots**

No screenshots.


