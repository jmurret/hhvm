<?hh
/* Prototype  : proto array array_count_values(array input)
 * Description: Return the value as key and the frequency of that value in input as value
 * Source code: ext/standard/array.c
 * Alias to functions:
 */

/*
 * Test behaviour with parameter variations
 */

class A {
    static function hello() {
      echo "Hello\n";
    }
}
<<__EntryPoint>> function main(): void {
echo "*** Testing array_count_values() : parameter variations ***\n";

$ob = new A();

$fp = fopen("array_count_file", "w+");

$arrays = array ("bobk" => "bobv", "val", 6 => "val6",  $fp, $ob);

var_dump (@array_count_values ($arrays));
echo "\n";


echo "Done";
error_reporting(0);
unlink("array_count_file");
}
