<?hh
class bomb {
  static function go($n) {
   $backtrace = debug_backtrace(0);
   $backtrace[1]['args'][1] = 'bomb';
  }
}
<<__EntryPoint>> function main(): void {
call_user_func_array(array('bomb', 'go'), array(0));
echo "ok\n";
}
