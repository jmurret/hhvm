<?hh
/* Prototype  : string stripcslashes  ( string $str  )
 * Description: Returns a string with backslashes stripped off. Recognizes C-like \n, \r ...,
 *              octal and hexadecimal representation.
 * Source code: ext/standard/string.c
*/

/*
 * Test stripcslashes() with non-string type argument such as int, float, etc
*/

// declaring a class
class sample  {
  public function __toString() {
  return "obj'ct";
  }
}
<<__EntryPoint>> function main(): void {
echo "*** Testing stripcslashes() : with non-string type argument ***\n";
// initialize all required variables

// get an unset variable
$unset_var = 'string_val';
unset($unset_var);

// Defining resource
$file_handle = fopen(__FILE__, 'r');

// array with different values
$values =  array (

          // integer values
/*1*/      0,
          1,
          12345,
          -2345,

          // float values
/*5*/      10.5,
          -10.5,
          10.1234567e10,
          10.7654321E-10,
          .5,

          // array values
/*10*/      array(),
          array(0),
          array(1),
          array(1, 2),
          array('color' => 'red', 'item' => 'pen'),

          // boolean values
/*15*/      true,
          false,
          TRUE,
          FALSE,

          // empty string
/*19*/      "",
          '',

          // undefined variable
/*21*/      $undefined_var,

          // unset variable
/*22*/      $unset_var,

          // objects
/*23*/      new sample(),

          // resource
/*24*/      $file_handle,

          // null values
/*25*/      NULL,
          null
);


// loop through each element of the array and check the working of stripcslashes()
// when $str argument is supplied with different values
echo "\n--- Testing stripcslashes() by supplying different values for 'str' argument ---\n";
$counter = 1;
for($index = 0; $index < count($values); $index ++) {
  echo "-- Iteration $counter --\n";
  $str = $values [$index];

  try { var_dump( stripcslashes($str) ); } catch (Exception $e) { echo "\n".'Warning: '.$e->getMessage().' in '.__FILE__.' on line '.__LINE__."\n"; }

  $counter ++;
}

// closing the file
fclose($file_handle);

echo "===DONE===\n";
}
