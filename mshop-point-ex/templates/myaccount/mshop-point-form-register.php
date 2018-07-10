<?php

$description = apply_filters( 'mshop_point_translate_string', get_option('mshop_point_system_recommender_point_register_description', '회원가입 안내문구를 입력하세요.'), 'point_register_message' );

if ( isset($description) ) {
    ?>
    <p style="font-size: 12px !important;"><?php echo str_replace( "\n", '<br>', $description); ?></p>
    <?php
}

