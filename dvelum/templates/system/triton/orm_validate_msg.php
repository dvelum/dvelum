<?php
if(!defined('DVELUM'))exit;
?>
<div>
    <h3><?php echo $this->lang->ORM_CNHANGES_WB_MADE;?>:</h3>
    <hr>
    <?php
    if(!$this->tableExists){
        echo $this->lang->ORM_TABLE_WB_CREATED;
    }

    if(!empty($this->engineUpdate))
    {
        echo '<h4>Table Engine will be updated:</h4><ul class="ormUl">' , $this->engineUpdate , '</ul>';
    }

    if(isset($this->columns) && !empty($this->columns)){

        $addStr='';
        $deleteStr='';
        $changeStr='';

        foreach ($this->columns as $item){
            switch ($item['action']){
                case 'add'    : $addStr.= '<li>' . $item['name'] . '</li>';
                    break;
                case 'change' : $changeStr.= '<li>' . $item['name'] . '</li>';
                    break;
                case 'drop' : $deleteStr.= '<li>' . $item['name'] . '</li>';
                    break;
            }
        }

        if(strlen($deleteStr))
            echo '<h4>Fields to be deleted:</h4><ul class="ormUl">' , $deleteStr , '</ul>';

        if(strlen($addStr))
            echo '<h4>Fields to be added:</h4><ul class="ormUl">' , $addStr , '</ul>';

        if(strlen($changeStr))
            echo '<h4>Fields to be updated:</h4><ul class="ormUl">' , $changeStr , '</ul>';
    }

    if(isset($this->indexes) && !empty($this->indexes))
    {
        $addStr='';
        $updateStr='';
        $deleteStr='';

        foreach ($this->indexes as $item){
            switch ($item['action']){
                case 'add'    : $addStr.='<li>' . $item['name'] . '</li><br>';
                    break;
                case 'drop' : $deleteStr.='<li>' . $item['name'] . '</li><br>';
                    break;
            }
        }

        if(strlen($deleteStr))
            echo '<h4>Indexes to be deleted:</h4><ul class="ormUl">' , $deleteStr , '</ul>';

        if(strlen($addStr))
            echo '<h4>Indexes to be added:</h4><ul class="ormUl">' , $addStr , '</ul>';
    }


    if(isset($this->keys) && !empty($this->keys))
    {
        $addStr='';
        $updateStr='';
        $deleteStr='';

        foreach ($this->keys as $item)
        {
            switch ($item['action'])
            {
                case 'add'    :
                    $msg = '`'. $item['config']['curDb'].'`.`'.$item['config']['curTable'].'` (`' . $item['config']['curField'] . '`)
						REFERENCES `'. $item['config']['toDb'].'`.`' . $item['config']['toTable'] . '` (`' . $item['config']['toField'] . '`)<br>
						ON UPDATE ' . $item['config']['onUpdate'] .'<br>
						ON DELETE ' . $item['config']['onDelete'];
                    $addStr.='<li>' . $msg . '</li><br>';
                    break;
                case 'drop' : $deleteStr.='<li>' . $item['name'] . '</li><br>';
                    break;
            }
        }

        if(strlen($deleteStr))
            echo '<h4>Foreign keys to be deleted:</h4><ul class="ormUl">' , $deleteStr , '</ul>';

        if(strlen($addStr))
            echo '<h4>Foreign keys to be added:</h4><ul class="ormUl">' , $addStr , '</ul>';
    }

    if(isset($this->objects) && !empty($this->objects))
    {
        $addStr='';
        $deleteStr='';

        foreach ($this->objects as $item){
            switch ($item['action']){
                case 'add'  : $addStr.='<li>' . $item['name'] . '</li><br>';
                    break;
                case 'drop' : $deleteStr.='<li>' . $item['name'] . '</li><br>';
                    break;
            }
        }

        if(strlen($deleteStr))
            echo '<h4>Objects to be deleted:</h4><ul class="ormUl">' , $deleteStr , '</ul>';

        if(strlen($addStr))
            echo '<h4>Objects to be added:</h4><ul class="ormUl">' , $addStr , '</ul>';
    }

    ?>
</div>