=== Classroom Library ===
Contributors: mdburnette
Tags: books, library, education, classroom
Requires at least: 5.0
Tested up to: 6.4.3
Requires PHP: 7.0
Stable tag: 0.1.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Classroom library plugin to catalog books and create a check in/out system for students.

== Description ==
Classroom library plugin to catalog books and create a check in/out system for students.

Quick entry of publications: Using the built-in barcode lookup, you can use a scanner or enter a barcode to pull book details from OpenLibrary.org automatically - no API keys needed!

Visual directory: Use a shortcode to place your library listing on any page you'd like!

== Installation ==
1. Upload the classroom-library plugin to your site and activate it!
2. Add new publications manually or by looking them up using the built-in barcode search (uses OpenLibrary.org).
3. Add the [bookshelf] shortode to any page you'd like the library to appear.
    - Add "search" attribute ("yes"/"no") to show/hide search bar. Default: "yes"
    - Add "cols" attribute to determine number of columns, or books per shelf row. Default: 6
4. Check available copies out and check borrowed books back in from individual publication pages.

== Screenshots ==
1. Publication barcode scanning
2. Single publication template
3. Bookshelf visually listing all publications

== Changelog ==
= 0.1.4 (March 28, 2024) =
* Fix: enable bookshelf search to work when using plain permalinks (ex: ?page=123)
* Misc: added documentation for bookshelf shortcode attributes

= 0.1.3 (March 28, 2024) =
* Fix: clean up errors when trying to save without IDs or field names
* Fix: implementation/override of single publication template

= 0.1.2 (???) =
* I'm sure I fixed something?

= 0.1.1 (August 19, 2022) =
* Fix: better sanitization
* Tweak: link to local image for missing cover
* Misc: better documentation and imagery for .org listing

= 0.1.0 (August 17, 2022) =
* Initial plugin release