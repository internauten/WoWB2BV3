# PrestaShop Group Price Text Module

A PrestaShop module that displays custom text on product pages for customers belonging to specific groups.

## Features

- Display custom messages on product pages based on customer group membership
- Displays Text only if there is no reduction for this group
- Displays the original catalog prise to if there is a reduction
- Easy configuration via module settings
- Enable/disable functionality
- Responsive Bootstrap styling
- Compatible with PrestaShop 1.7+

## Installation

1. Copy the `modules/grouppricetext` folder to your PrestaShop installation
2. Go to Back Office → Modules → Module Manager
3. Find "Group Price Text" and click Install
4. Configure the module settings

## Configuration

- **Enable module**: Toggle the module on/off
- **Customer Group**: Select which customer group will see the message
- **Message**: Enter the text to display to the selected group

## How It Works

The module uses the `displayProductPriceBlock` hook to display text after the product price. It checks if the logged-in customer belongs to the configured group and displays the custom message accordingly.

## License

Academic Free License (AFL 3.0)