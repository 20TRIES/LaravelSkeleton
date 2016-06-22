<?php

/**
 * Prep URL
 *
 * Simply adds the http:// part if no scheme is included
 *
 * @access	public
 * @param	string	the URL
 * @return	string
 */
if ( ! function_exists('prep_url'))
{
    function prep_url($str = '')
    {
        if ($str == 'http://' OR $str == '')
        {
            return '';
        }

        $url = parse_url($str);

        if ( ! $url OR ! isset($url['scheme']))
        {
            $str = 'http://'.$str;
        }

        return $str;
    }
}

/**
 * Segments a url.
 *
 * Splits a url into its segment components.
 *
 * @access	public
 * @param	string	the URL
 * @return	string
 */
if ( ! function_exists('segment_url'))
{
    function segment_url($str = '')
    {
        $segments = [];

        foreach (explode("/", preg_replace("|/*(.+?)/*$|", "\\1", $str)) as $val)
        {
            if ($val != '')
            {
                $segments[] = $val;
            }
        }

        return $segments;
    }
}