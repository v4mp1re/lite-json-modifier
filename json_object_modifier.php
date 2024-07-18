<?php
/**
 * Modify the object data from large json files.
 * 
 * This function aim to reduce memory overhead of json_decode for large json files
 *
 * @param string $file file which stores json data
 * @param string $key key of the object to be modified
 * @param array $attributes new attributes of the object
 * @return int|false
 */
function jsonObjectModifier($file, $key, $attributes)
{
    define('JSON_KEY_VAL_SEPERATOR', ':');
    $content = file_get_contents($file);

    #modifiying key to json data format
    $json_key = '"' . $key . '"';

    $seeker = strpos($content, $json_key);

    $object_stack = [];
    $data = '';

    while ($content[$seeker] !== null) {
        $data .= $content[$seeker];

        # push to object stack when new object data starts
        if ($content[$seeker] === '{') {
            $object_stack[] = '{';
        }

        # pop from object stack when object data ends
        if ($content[$seeker] === '}') {
            array_pop($object_stack);
            # object boundary reached
            if (!count($object_stack)) {
                break;
            }
        }

        $seeker++;
    }

    # getting the property string of the key object
    $prop_string = substr($data, strlen($json_key . JSON_KEY_VAL_SEPERATOR));

    # convert into array for manipulation
    $properties = json_decode($prop_string, true);

    # modify the existing properties with new properties
    $properties = array_merge($properties, $attributes);

    $prop_string = json_encode($properties);

    $content = str_replace($data, $json_key . JSON_KEY_VAL_SEPERATOR . $prop_string, $content);

    return file_put_contents($file, $content);
}
