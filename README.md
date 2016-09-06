# Storefront Aggregator

A flexible and extensible content Aggregator for Storefront... Improves user experience and adds dynamic content to your pages.

## Description

The aggregators are registered as custom post type - Edit and manipulate them as such.<br />
You can attach to aggregators the desired amount of items and choose on which pages to display each aggregator. It is possible to display multiple aggregators on a single page.<br />
By default, the available item types are 'Last Posts' and 'Last Comments'. Compatible with WooCommerce, you also can aggregate 'Last Products' or 'Last Reviews'.<br />
The aggregate items are slidable and fully responsive. Thanks to FlexSlider.<br />
Features a dedicated Customizer section from which you can switch color of all aggregator's elements.

**Coming soon**
- Possibility to display each aggregator to the page and its template action hook of YOUR choice. This will bring interesting perspectives...
- Dynamically generated Customizer settings for **each** Aggregator including different choice of layouts and further styles.

## Extensibility

For devs, it allows seamless integration of **any** imaginable custom items. Just few examples:
- Last orders and/or last order notes on WooCommerce account page.
- Category items.
- RSS feeds.
- Etc... The possibilities are endless.

All this is possible with the help of 2 filter hooks - `storefront_aggregator_meta_boxes` -  `storefront_aggregator_query_items` and 1 action hook - `storefront_aggregator_item_template`.<br />
You can take `integration/woocommerce` as reference for the integration of your custom items.

## Changelog

**v0.1 - 01/09/2016**
- Beta Release

## Licence

MIT License

Copyright (c) 2016 Clément Cazaud

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
