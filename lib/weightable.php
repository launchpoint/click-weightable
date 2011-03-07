<?

function codegen_weightable_move($objects, $target, $direction)
{
  $max_weight=$target->max_weight_for($objects);
  $objects = weightable_fix_object_weights($objects);
  
  for($i=0;$i<count($objects);$i++)
  {
    if($target->id != $objects[$i]->id) continue;
    switch(p('direction'))
    {
      case 'top':
        foreach($objects as $o)
        {
          if($o->weight < $objects[$i]->weight)
          {
            $o->weight++;
          }
        }
        $objects[$i]->weight = 1;
        $objects[$i]->save();
        break;
      case 'bottom':
        foreach($objects as $o)
        {
          if($o->weight > $objects[$i]->weight)
          {
            $o->weight--;
          }
        }
        $objects[$i]->weight = $max_weight;
        break;
      case 'up':
        $objects[$i-1]->weight++;
        $objects[$i]->weight--;
        break;
      case 'down':
        $objects[$i+1]->weight--;
        $objects[$i]->weight++;
        break;
    }
  }
  foreach($objects as $o) $o->save();
}


function weightable_fix_object_weights($objects)
{
  usort($objects, 'weightable_compare');
  $i=1;
  foreach($objects as $o) $o->weight=$i++;
  return $objects;
}

function weightable_compare($a, $b)
{
  if ($a->weight == $b->weight) {
      return 0;
  }
  return ($a->weight < $b->weight) ? -1 : 1;
}

function codegen_weightable_max_weight_for($unused, $objects)
{
  $max_weight=0;
  foreach($objects as $o) $max_weight=max($o->weight, $max_weight);
  return $max_weight;
}

function codegen_weightable_ensure_weight($event_args, $event_data, $object_name)
{
  $o = $event_args[$object_name];
  if(!$o->weight)
  {
    $recs = query_assoc("select max(weight) c from !", eval("return {$o->klass}::\$table_name;"));
    $o->weight = max(1,$recs[0]['c']);
  }
}
