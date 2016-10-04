# Storefront Aggregator

A flexible and extensible content Aggregator for Storefront... Improves user experience and adds dynamic content to your pages.

## Description

The aggregators are registered as custom post type - Edit and manipulate them as such.<br />
You can attach to aggregators the desired type and amount of items and choose on which pages and which template action hook to display each aggregator... It is possible to add multiple aggregators on a single page and/or on a single template hook.<br />
By default, the available item types are 'Last Posts' and 'Last Comments'. Compatible with WooCommerce, you also can aggregate 'Last Products' or 'Last Reviews'.<br />
The aggregate items are slidable and fully responsive. Thanks to FlexSlider.<br />
Features a dedicated Customizer panel in which each aggregator can be deeply customized.<br />

## Focus

The main points I've focused on are **extensibility** and **flexibility**.

**Extensibility**

At this stage, any tier developer can add his custom items to aggregate via 3 hooks:

 - `aggregator_meta_boxes` filter from which we can add custom items types to be selected for aggregation.
 - `aggregator_items` filter from which we can add the item objects themselves.
 - `aggregator_items_template` action for hooking a custom item template easily enough inside the aggregator template.

The custom item integration process could optionally get simplified via a little API.

**Flexibility**

From the setting page, you can display the aggregator via **any** template action hook of your choice. Of course to adapt its layout to every possible hooks, a Customize section is available for each aggregator. 

## Coming soon

- Improved Customizer... More user friendly interface and further styling options such as aggregator layout, margins, etc...

## Ideas

Good or bad ideas. Feedbacks are welcome.

- Make aggregator's items editable and buildable directly by the end user via a setting subpage.

## Changelog

**v0.2 - 14/09/2016**
- Feature: Possibility to display each aggregator to **any** page and **any** template action hook of **your** choice.
- Feature: Possibility to Customize **each** aggregator.

**v0.1 - 01/09/2016**
- Beta Release

## Licence

MIT License

Copyright (c) 2016 Clément Cazaud

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
