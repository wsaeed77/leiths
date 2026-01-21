<p>
    <label for="<?php echo $widget->get_field_id("title") ?>">
    <?php _e("Title", "wpjobboard") ?>
    <?php Daq_Helper_Html::build("input", array(
        "id" => $widget->get_field_id("title"),
        "name" => $widget->get_field_name("title"),
        "value" => $instance["title"],
        "type" => "text",
        "class"=> "widefat",
        "maxlength" => 100
    )); 
    ?>
   </label>
</p>

<p>
   <label for="<?php echo $widget->get_field_id("frequency_default") ?>">
   <?php _e("Default Alerts Frequency", "wpjobboard") ?>
   <?php 
        $field = new Daq_Form_Element_Select($widget->get_field_name("frequency_default"));
        $field->addOptions([
            ["value" => 1, "description" => __("Daily", "wpjobboard")],
            ["value" => 2, "description" => __("Weekly", "wpjobboard")],
        ]);
        $field->setMaxChoices(1);
        $field->setValue(isset($instance["frequency_default"]) ? $instance["frequency_default"] : 1);
        $field->addClass("widefat");
        echo $field->render(); 
    ?>
   </label>
</p>

<p>
   <label for="<?php echo $widget->get_field_id("frequency_change") ?>">
   <?php _e("Allow changing alerts frequency", "wpjobboard") ?>
   <?php Daq_Helper_Html::build("input", array(
       "id" => $widget->get_field_id("frequency_change"),
       "name" => $widget->get_field_name("frequency_change"),
       "checked" => (int)$instance["frequency_change"],
       "value" => 1,
       "type" => "checkbox",
       "class"=> ""
   )); 
   ?>
   </label>
</p>

<p>
   <label for="<?php echo $widget->get_field_id("hide") ?>">
   <?php _e("Show on job board only", "wpjobboard") ?>
   <?php Daq_Helper_Html::build("input", array(
       "id" => $widget->get_field_id("hide"),
       "name" => $widget->get_field_name("hide"),
       "checked" => (int)$instance["hide"],
       "value" => 1,
       "type" => "checkbox",
       "class"=> ""
   )); 
   ?>
   </label>
</p>

<p>
   <label for="<?php echo $widget->get_field_id("smart") ?>">
   <?php _e("Enable smart alerts", "wpjobboard") ?>
   <?php Daq_Helper_Html::build("input", array(
       "id" => $widget->get_field_id("smart"),
       "name" => $widget->get_field_name("smart"),
       "checked" => (int)$instance["smart"],
       "value" => 1,
       "type" => "checkbox",
       "class"=> ""
   )); 
   ?>
   </label>
</p>
