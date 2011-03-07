<?
if(p("{$namespace}[cmd]")!='move') return;

foreach($params as $k=>$v)
{
  if(!startswith($k, 'superlist_')) continue;
  $order = array();
  foreach($v as $elem)
  {
    $parts = split('_', $elem);
    $id = array_pop($parts);
    if(!$id) continue;
    $order[]=$id;
  }
  $ids = collect($objects, 'id');
  foreach($objects as $o)
  {
    $pos = array_search($o->id, $order)+1;
    if($pos != $o->weight)
    {
      $o->weight = $pos;
      $o->save();
    }
  }
}
