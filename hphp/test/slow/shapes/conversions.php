<?hh

$s = shape('a' => 2, 'b' => 3);


var_dump(array($s));
var_dump(darray($s));
var_dump(varray($s));
var_dump(dict($s));
var_dump(vec($s));

var_dump((array)$s);
var_dump((int)$s);
var_dump((double)$s);
var_dump((bool)$s);
var_dump((string)$s);

var_dump(is_array($s));
var_dump(is_darray($s));
var_dump(is_varray($s));
var_dump(is_dict($s));
var_dump(is_vec($s));

var_dump(is_array(array($s)));
var_dump(is_darray(darray($s)));
var_dump(is_varray(varray($s)));
var_dump(is_dict(dict($s)));
var_dump(is_vec(vec($s)));

var_dump(is_array((array)$s));
