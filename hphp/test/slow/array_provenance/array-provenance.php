<?hh

function append($a) {
  $a = __hhvm_intrinsics\launder_value($a);
  $a[] = "hello";
  var_dump($a);
  var_dump(HH\get_provenance($a));
}

function setelem_int($a) {
  $a = __hhvm_intrinsics\launder_value($a);
  $a[42] = "hello";
  var_dump($a);
  var_dump(HH\get_provenance($a));
}

function setelem_string($a) {
  $a = __hhvm_intrinsics\launder_value($a);
  $a["blargh!"] = "goodbye";
  var_dump($a);
  var_dump(HH\get_provenance($a));
}

function setelem_invalid($a) {
  $a = __hhvm_intrinsics\launder_value($a);
  $a[false] = "eek";
  var_dump($a);
  var_dump(HH\get_provenance($a));
}

function elemd_int($a) {
  $a = __hhvm_intrinsics\launder_value($a);
  $a[0] += 5;
  var_dump($a);
  var_dump(HH\get_provenance($a));
}

class ConstBag {
  const string MY_TRASH_GARBAGE = 'crap';
}

<<__EntryPoint>>
function main() {
  append(
    vec[rand()]
  );
  append(
    dict['trash' => rand()]
  );
  setelem_int(
    dict['trash' => rand()]
  );
  setelem_string(
    dict['trash' => rand()]
  );
  elemd_int(
    dict[0 => 10]
  );
  elemd_int(
    vec[10]
  );

  $a = __hhvm_intrinsics\launder_value(
    dict['trash' => rand()]
  );
  $a["foo"] = dict['trash' => rand()];
  $a["foo"]["bar"] = 42;
  var_dump(HH\get_provenance($a));
  var_dump(HH\get_provenance($a["foo"]));

  $b = __hhvm_intrinsics\launder_value(
    dict[
      42 => dict['trash' => rand()]
    ]
  );
  $b[42][42] = "fasdf";
  var_dump(HH\get_provenance($b));
  var_dump(HH\get_provenance($b[42]));

  $c = __hhvm_intrinsics\launder_value(
    dict[
      42 => vec[rand()]
    ]
  );
  $c[42][] = rand();
  var_dump(HH\get_provenance($c));
  var_dump(HH\get_provenance($c[42]));

  $d = __hhvm_intrinsics\launder_value(
    __hhvm_intrinsics\dummy_dict_builtin(
      dict[
        42 => vec[rand()]
      ]
    )
  );
  $d[42][] = rand();
  var_dump(HH\get_provenance($d));
  var_dump(HH\get_provenance($d[42]));

  $e = __hhvm_intrinsics\launder_value(
    dict[ConstBag::MY_TRASH_GARBAGE => $d]
  );
  var_dump(HH\get_provenance($e));

  $f = __hhvm_intrinsics\launder_value(
    Vector { rand(), rand() }
  );
  $f1 = vec($f);
  $f2 = dict($f);
  $f3 = $f->toMap();
  $f4 = dict($f3);
  var_dump(HH\get_provenance($f1));
  var_dump(HH\get_provenance($f2));
  var_dump(HH\get_provenance($f4));

  $g = __hhvm_intrinsics\launder_value(
    Map { 'foo'.rand() => rand() }
  );
  $g1 = dict($g);
  var_dump(HH\get_provenance($g1));
}
