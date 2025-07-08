# HdK Spektrix Plugin

## Licensing Information

This is licenced under a xxx licence 2021

## Installation

Install like a standard Wordpress plugin.

This plugin makes use of WP-CRON to populate the database with Spektrix data. This may cause PHP timeouts if you have many Spektrix events/instances. It's highly recommended to unhook WP-CRON and rehook it to the system cron. See [WP docs](https://developer.wordpress.org/plugins/cron/hooking-wp-cron-into-the-system-task-scheduler/), though use php -q /home/{site}/public_html/wp-cron.php rather than wget.

## Post-Installation Setup

After installing you can enter Spektrix details into the Spektrix Options admin menu. You'll also find options for post-types, event attributes and cron jobs. After the data is pulled for the first time, click Refresh Wordpress Data to create Posts from the Spektrix data

You can refresh the entire Spektrix DB from this admin menu, or refresh a single event. 


## Pulling Data

The cron job triggers the UpdateTables() method in the Populate class. This method pulls all event data from the Spektrix API, iterates over it and inserts it into the database. If this is succesful, it then calls the UpdatePosts() method which will create new posts from the data if they don't exist, and update taxonomies and post meta if they do.

If you need to pull data in between cron job runs, there is a Refresh Spektrix Data button in the admin menu. This does a similar task to above, but breaks it into batches to avoid php timeouts. This can take a while. When it is finished you must manually update posts by clicking Refresh Wordpress Data.

You can also update a Single Event. This will pull fresh data for a single event, and update the post taxonomies and post meta.

## Description

HdK Spektrix Plugin pulls event and merchandise details from the Spektrix API and adds them to tables in the WP database. It then creates Events and Merchandise custom post types (as defined in Spektrix Options) and populates the posts with data. 

HdK Spektrix Plugin makes some classes and methods available for templating and processing.

## Using the HdK Spektrix classes

### HdKSpektrix

This is the main class allowing us the access the plugins functionality. You can retrieve data about merchandise and events (though this is presented in more useful ways in the SpektrixEvents and SpektrixMerch classes). You can also retrieve [web components](https://webcomponents.spektrix.com/docs/quickstartdonations.html) and the newsletter form. See utilities below if you need to adjust the markup for these. Available methods:
- get_event_id($post_id),get_merch_id($post_id): _int._ Gets the event or merch id from the post id. Is mainly used by the SpektrixEvents/Spektrix Merch class
- get_event_short_id($post_id), get_merch_short_id($post_id): _int._ Some API calls require a shortened version of the Spektrix id. This will return the processed short id by post id
- get_date_range($id): _string._ Pulls the date range from the DB by event id
- get_event_data($id): _array._ Returns an array of all rows from the DB for an event by event id
- get_event_instances($id): _array._ Returns an arraw of all rows for each instance called by event id.
- get_event_price_range($ID): _array._ Returns an array containing the minimum and maximum price range for an event.
- get_instance_ticket_types($id): _array._ Returns all ticket types available for an instance by instance id. This is necessary if you are making a booking directly through the API rather than using the iframes.
- get_merch_data($id): _array._ Returns an array of all rows from the DB for a merch item by merch id
- get_fund_id($name): _int._ Gets the donation fund ID by name. Used by the Donations Web Component
- get_newsletter_tags(): _array._ Returns and array of names and ids for newsletter tags. Used by the newsletter form
- get_newsletter_form(): _string._ Returns the markup for the newsletter form. Markup can be adjusted in the Utilities class.
- get_memberships(): _string._ Returns all data for Spektrix memberships used by the Memebrship web component.
- get_donate_component(): _string._ Returns the Donate Web Component. The name of the fund needs to be hardcoded in here. This could be moved into a form in the Spektrix Options if there is more than one relevant fund.
- get_members_component(): _string._ Returns the Members Web Component. Markup can be adjusted in the Utilities class.
- get_basket_summary_component(): _string._ Returns the Basket Summary Web Component. Markup can be adjusted in the Utilities class.
- get_login_status_component(): _string._ Returns the Login Status Web Component. Markup can be adjusted in the Utilities class.


### SpektrixEvents

This class inherits from SpektrixItem and uses HdKSpektrix as a basis. It needs to be called with the current post_id. Available methods:
- get_dates(): _array._ Returns an array containing the start date, end date and a date range. The formatting of these dates is pulled from Spektrix.
- get_instances(): _array._ Returns an array of instance arrays containing details for each instance of an event
- get_instances_ids(): _array._ Returns an array of instance ids
- get_instances_dates(): _array._ Returns an array of start dates and ids of instances that are available
- get_instances_dates_aggregated(): _array._ Returns an array of months that contain instances. Useful for setting up a calendar
- get_instances_by_attribute($attribute): _array._ Returns an array of start dates and ids of instances that are available by attribute (not used in Bowes)
- get_instance_ticket_types($id): _array._ Requires the instance ID. Returns an array of arrays containing "instanceID", "seatingPlan" and "ticketType" for each ticket type. The array is keyed by the ticket type name. These are the details needed to be sent to the API to add to basket. Each array also includes the price for display purposes.
- get_availability_for_block_booking(),get_instances_block_tickets(),is_block_booking_available(): These four methods provide data to make a custom block booking path. They'll probably need to be tweaked to match the project and block booking requirements (not used in Bowes)
- get_price_range(): _array._ Returns an array of minimum and maximum prices for the event.
- get_add_tickets_to_basket_url(): _string._ Returns the API url to POST ticket data.
- is_sold_out(): _boolean._ Get availability data from the DB
- get_realtime_availability($instance): _boolean._ Check if an instance is available. This uses the Spektrix API rather than refering to the database, so it isn't using cached data


### SpektrixMerch

This class inherits from SpektrixItem and uses HdKSpektrix as a basis. It needs to be called with the current post_id. Available methods:
- get_price(): _string._ Returns the price including pound sign and two decimal places.
- is_sold_out(): _boolean._ Checks if the merch is sold out from the DB. Ie the data is cached.
- get_purchase_component(): _string._ Returns the merchandise purchase component, Markup can be adjusted in the Utilities class


### Utilities

There are a set of utilities used to display iframes and webcomponents. These are static methods in the class HdKSpUtilities, and are mostly called from the Spektrix class. Markup for forms, iframes and components are here if you need to adjust.

#### Web Components

These use Spektrix Web Components. See [here](https://webcomponents.spektrix.com/docs/quickstartdonations.html) for markup customisations. All of the web components polyfills and js are loaded by by the LoadWebComponents method of HdKSpFrontLoader. You may need to adjust the markup to match your project for each component, which is contained in the Utilities class.

#### iFrames

There are a set of standard iframes from Spektrix to handle baskets, checkout, accounts, cookie management and gift vouchers. See [here](https://integrate.spektrix.com/docs/standardbookingflow). These can be called individually, but they are loaded into a set of pages automatically. The relevant pages are controlled by Spektrix Options. Leave a field blank if you want to roll your own solution instead of the iframe, though checkout is required as this functionality isn't exposed by the Spektrix API.

Note, the Choose Seats iframe requires an instance ID, not an event ID.

#### Forms
The Utilities class also holds a newsletter signup form method, where you can adjust the markup if needed.

