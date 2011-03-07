<?

foreach($models as $model_klass)
{
  $t = singularize(tableize($model_klass));
  $table_name = eval("return $model_klass::\$table_name;");
  
  $hm = eval("return $model_klass::\$has_many;");

  foreach($hm as $hm_name=>$hm_info)
  {
    $hm_klass = singularize(classify($hm_info[0]));
    $types = eval("return $hm_klass::\$attribute_types;");
    if(!isset($types['weight'])) continue;
    $name = singularize($hm_name);
    $code = <<<PHP
function {$t}_move_{$name}(\$o, \$target, \$direction)
{
  codegen_weightable_move(\$o->$hm_name, \$target, \$direction);
}    
PHP;
    $codegen[] = $code;
  }

  $types = eval("return $model_klass::\$attribute_types;");
  if(!isset($types['weight'])) continue;
  $code = <<<PHP

function weightable_{$t}_before_validate(\$event_args, \$event_data)
{
  return codegen_weightable_ensure_weight(\$event_args, \$event_data, '$t');
}

function weightable_{$t}_before_save(\$event_args, \$event_data)
{
  return codegen_weightable_ensure_weight(\$event_args, \$event_data, '$t');
}


function weightable_superlist_{$t}_columns(\$event_args, \$event_data)
{
  event('weightable_list_column', \$event_args);
}

function {$t}_max_weight_for__d(\$o, \$objects)
{
  return codegen_weightable_max_weight_for(\$o, \$objects);
}

function {$t}_get_max_weight__d(\$o)
{
  return \$o->max_weight_for(null);
}

function {$t}_get_is_top__d(\$o)
{
  return \$o->weight == 1;
}

function {$t}_is_bottom_for__d(\$o, \$objects)
{
  return \$o->weight == \$o->max_weight_for(\$objects);
}

function {$t}_is_bottom__d(\$o)
{
  return \$o->is_bottom_for(null);
}

PHP;
  $codegen[] = $code;
  
}
