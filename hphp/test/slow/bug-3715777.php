<?hh


function array_extend(inout $dest, array $src): void {
  foreach ($src as $element) {
    $dest[] = $element;
  }
}

function getImpliedChecks(array $arg): array {
  $implied_checks = array();
  foreach ($arg as $key => $val) {
    array_extend(inout $implied_checks, $val);
  }

  return $implied_checks;
}


<<__EntryPoint>>
function main_bug_3715777() {
for ($i = 0; $i < 10; $i++) {
  var_dump(getImpliedChecks(array(array(1,2,3), array(4,5,6), array(7,8,9))));
}
}
