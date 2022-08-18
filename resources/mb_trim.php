<?php
// custom made safe multibyte trimming

function mb_trim($string, $charlist='\\\\s', $ltrim=true, $rtrim=true) {
    $both_ends = $ltrim && $rtrim;

    $char_class_inner = preg_replace(
        array( '/[\^\-\]\\\]/S', '/\\\{4}/S' ),
        array( '\\\\\\0', '\\' ),
        $charlist
    );

    $work_horse = '[' . $char_class_inner . ']+';
    $ltrim && $left_pattern = '^' . $work_horse;
    $rtrim && $right_pattern = $work_horse . '$';

    if($both_ends)
    {
        $pattern_middle = $left_pattern . '|' . $right_pattern;
    }
    elseif($ltrim)
    {
        $pattern_middle = $left_pattern;
    }
    else
    {
        $pattern_middle = $right_pattern;
    }

    $cleaned = preg_replace("/$pattern_middle/usSD", '', $string);
    return $cleaned;
}
?>