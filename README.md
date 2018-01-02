# Joomla Namespace Checker
For migrating to Joomla 3.8.x

This script scans a directory recursively for PHP files containing calls to the old classes (e.g. JFactory).

The paths are filtered using the `$paths` array (line 12).
Every path that contains any value from the array is removed from the scan.

## Usage
To run the script, download it and execute it using php
```
php joomlaNamespaceChecker.php path/to/your/project
```

If any calls are found you will get the following output:
```
Class JFilterInput was found in somepath/somefile.php:lineNumber. Use Joomla\CMS\Filter\InputFilter instead.

Class JFilterInput was found in somepath/somefile.php:lineNumber. Use Joomla\CMS\Filter\InputFilter instead.

Class JFactory was found in somepath/somefile.php:lineNumber. Use Joomla\CMS\Factory instead.
```