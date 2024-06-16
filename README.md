# Register Custom Columns Example Plugin

This plugin demonstrates how to use register custom columns in a plugin.

## Installation

To install this plugin, clone the repository into your WordPress plugins
directory:

```bash
git clone git@github.com:arraypress/custom-register-columns-plugin.git
```

Navigate into the plugin directory:

```bash
cd register-custom-columns-plugin
```

Install the dependencies:

```bash
composer install
```

## Description

This plugin is an example implementation of the ArrayPress Register Custom Columns library. It demonstrates how to register custom columns for different WordPress objects including posts, comments, taxonomies, media, users, and Easy Digital Downloads (EDD) entities such as discounts, customers, and orders.

### Features

1. **Post Columns**
    - **Thumbnail:** Adds a thumbnail column before the title column. The thumbnail is displayed with a size of 64x64 pixels.
    - **Review Date:** Adds a review date column before the date column. This column is sortable and supports inline editing with a date picker.

2. **Comment Columns**
    - **Word Count:** Adds a word count column after the author column. This displays the number of words in the comment content.

3. **Taxonomy Columns**
    - **Color:** Adds a color column after the slug column. This column supports inline editing with a color picker and displays the selected color as a circle.
    - **Membership Level:** Adds a membership level column after the color column. This column is sortable and supports inline editing with a select dropdown.

4. **Media Columns**
    - **File Size:** Adds a file size column after the author column. This displays the file size of the attachment.
    - **File Type:** Adds a file type column after the file size column. This displays the type of the attachment file.
    - **File Extension:** Adds a file extension column after the file type column. This displays the file extension of the attachment.

5. **User Columns**
    - **Points:** Adds a points column for users. This column is sortable and supports inline editing with a number input field.

6. **EDD Columns**
    - **Discounts:**
        - **Savings:** Adds a savings column after the use count column. This displays the total savings for the discount.
        - **Earnings:** Adds an earnings column after the use count column. This displays the total earnings for the discount.
    - **Customers:**
        - **Average Order Value:** Adds an average order value column after the spent column. This displays the average order value for the customer.
    - **Orders:**
        - **Purchased:** Adds a purchased column after the customer column. This displays the items purchased in the order with a tooltip showing detailed information.

## Contributions

Contributions to this library are highly appreciated. Raise issues on GitHub or submit pull requests for bug
fixes or new features. Share feedback and suggestions for improvements.

## License: GPLv2 or later

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public
License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
