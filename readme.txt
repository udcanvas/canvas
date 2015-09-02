Four major parts.

1. SELECTIVELY copy files from working folder into the git folder. Only copy index.php, shared.php and any dependent files you see by recursively searching for .php|.html. We don't want any unused files in the git folder!

2. Perform all find and replace steps (see below)

3. Merge shared and inhouse versions by creating a common.php file with appropriate branching.

4. Install on udel.test.instructure.com or udel.beta.instructure.com and test, relocating updated xml file to git folder as necessary. All inhouse config files should be renamed to config.xml, all shared configs to shared.xml

-------------in xml files, remove any nodes that set shared=1--------------------
find   <lticm:property name="shared">1</lticm:property>  and DELETE

-----------find and replace all file paths for both html and php---------------

php replacement paths - always replace site relative php paths FIRST

site relative /www/git/lti/<folder> replaces /www/canvas/<folder> or /www/LTI436

html replacements /git/lti/<folder> replaces /canvas/<folder>

check for strays: search for .php


-------------find and replace all calls to php fns beginning with mysql_--------------

overview - we use 8 - 10 mysql functions in php that have been deprecated in favor of mysqli functions. Most of the new functions have the same name, but with some important differences.

Category 1 : same name, but different parameters. Old fns had an option $link param, usually second, but would infer your most recent $link if it was omitted. New version requires $link as first param.

real_escape_string - reverse order of params! ($link,string)
query - ($link,string)
error - ($link)
close - ($link)

find: mysql_(real_escape_string|error|query|close)\(
 
and replace with:   mysqli_$1($link,

but be careful, in case original might have had the $link param as second

----------------------------------------------

Category 2 - no direct replacement

mysql_result($result,0) -> mysqli_fetch_field($result)

Category 3 - direct replacement, these are mostly those that took $result as a lone argument

num_rows - direct conversion
mysql_fetch_assoc - direct conversion
mysql_fetch_array -direct conversion

find: mysql_(fetch_assoc|fetch_array|num_rows) 

and replace with: mysqli_$1
