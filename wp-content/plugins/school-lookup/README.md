# School Lookup WordPress Plugin

A WordPress plugin that allows administrators to upload and manage a database of schools via CSV files, and provides a searchable dropdown interface on the frontend for users to find and select schools.

## Features

- **Admin Interface**: Upload CSV files containing school data (supports ~20,000+ schools)
- **Database Management**: Efficient storage and retrieval of school information
- **Frontend Search**: Beautiful, responsive searchable dropdown matching the Sparx Learning design
- **AJAX Search**: Fast, real-time search as users type
- **Keyboard Navigation**: Full keyboard support (arrow keys, enter, escape)
- **Responsive Design**: Works on desktop and mobile devices

## Installation

1. Upload the `school-lookup` folder to `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to **School Lookup** in the WordPress admin menu

## Usage

### Admin: Uploading School Data

1. Navigate to **School Lookup** in the WordPress admin menu
2. Prepare your CSV file with the following columns (in this order):
   - URN
   - LA (code)
   - LA (name)
   - EstablishmentNumber
   - EstablishmentName
   - TypeOfEstablishment (name)
   - EstablishmentStatus (name)
   - ReasonEstablishmentOpened (name)
   - OpenDate
   - PhaseOfEducation (name)
   - StatutoryLowAge
   - StatutoryHighAge
   - Boarders (name)
   - OfficialSixthForm (name)
   - Gender (name)
   - ReligiousCharacter (name)
   - AdmissionsPolicy (name)
   - UKPRN
   - Street
   - Locality
   - Address3
   - Town
   - County (name)
   - Postcode
   - SchoolWebsite
   - TelephoneNum
   - HeadTitle (name)
   - HeadFirstName
   - HeadLastName
   - HeadPreferredJobTitle
   - GOR (name)
   - ParliamentaryConstituency (code)
   - ParliamentaryConstituency (name)

3. Click **Choose File** and select your CSV file
4. Optionally check **Clear Existing Data** to replace all current schools
5. Click **Upload CSV**
6. Wait for the import to complete (large files may take a few minutes)

### Frontend: Displaying the Search

Add the shortcode to any page, post, or widget:

```
[school_lookup]
```

#### Shortcode Parameters

- `placeholder` - Custom placeholder text (default: "Start typing the name of your school to begin searching.")
- `min_chars` - Minimum characters before search starts (default: 2)
- `max_results` - Maximum number of results to display (default: 10)

Example:
```
[school_lookup placeholder="Search for your school..." min_chars="3" max_results="15"]
```

### JavaScript Events

The plugin triggers a custom event when a school is selected:

```javascript
jQuery(document).on('school_lookup_selected', function(event, data) {
    console.log('Selected School ID:', data.id);
    console.log('Selected School Name:', data.name);
    // Your custom code here
});
```

### Accessing Selected School Data

The plugin stores the selected school in hidden fields:
- `school_id` - The database ID of the selected school
- `school_name` - The name of the selected school

You can access these values via JavaScript or form submission.

## Database

The plugin creates a custom table `wp_school_lookup` (prefix may vary) to store all school data. The table includes indexes for fast searching.

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- MySQL 5.6 or higher

## File Structure

```
school-lookup/
├── school-lookup.php          # Main plugin file
├── includes/
│   ├── class-school-lookup-database.php
│   ├── class-school-lookup-admin.php
│   └── class-school-lookup-frontend.php
├── assets/
│   ├── css/
│   │   ├── admin.css
│   │   └── frontend.css
│   └── js/
│       └── frontend.js
└── README.md
```

## Support

For issues or questions, please contact the plugin developer.

## License

GPL v2 or later
