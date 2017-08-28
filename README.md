# Contao Custom Collection


## Use
1. Extend the `tl_custom_collection.php` table:
   Add a custom subpalette for each collection. The name must correspond to `type_<collection_archive>`, whereas collection_archive is the name of your collection archive in snake case. E.g. for an archive called "Example Collection":
   ```php
   $GLOBALS['TL_DCA']['tl_custom_collection']['subpalettes']['type_example_collection'] = ';{image_legend},addImage;{text_legend},text;';
   ```
   You can thereby chose from numerous predefined fields, or, add your custom fields to the `fields` array, e.g.:
   ```php
   $GLOBALS['TL_DCA']['tl_custom_collection']['fields']['customTextField'] = [
     'label'                   => &$GLOBALS['TL_LANG']['tl_custom_collection']['customTextField'],
     'exclude'                 => true,
     'search'                  => true,
     'sorting'                 => true,
     'inputType'               => 'text',
     'eval'                    => array('mandatory'=>false, 'maxlength'=>255),
     'sql'                     => "varchar(255) NOT NULL default ''"
   ];
   ```
2. After install and DB update, create a custom collection archive in the Contao Backend.
