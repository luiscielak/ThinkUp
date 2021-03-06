<?php
/**
 * Post
 * A post, tweet, or status update on a ThinkUp source network or service (like Twitter or Facebook)
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class Post {
    var $id;
    var $post_id;
    var $author_user_id;
    var $author_fullname;
    var $author_username;
    var $author_avatar;
    var $post_text;
    var $source;
    var $location;
    var $place;
    var $geo;
    var $pub_date;
    var $adj_pub_date;
    var $in_reply_to_user_id;
    /**
     *
     * @var bool
     */
    var $is_reply_by_friend;
    var $in_reply_to_post_id;
    var $reply_count_cache;
    var $in_retweet_of_post_id;
    var $retweet_count_cache;
    /**
     *
     * @var int
     */
    var $reply_retweet_distance;
    /**
     *
     * @var bool
     */
    var $is_retweet_by_friend;
    var $network;
    /**
     *
     * @var int 0 if Not Geoencoded, 1 if Successful, 2 if ZERO_RESULTS,
     * 3 if OVER_QUERY_LIMIT, 4 if REQUEST_DENIED, 5 if INVALID_REQUEST, 6 if INSUFFICIENT_DATA 
     */
    var $is_geo_encoded;
    var $author; //optional user object
    var $link; //optional link object

    /**
     * Constructor
     * @param array $val Array of key/value pairs
     */
    public function __construct($val) {
        $this->id = $val["id"];
        $this->post_id = $val["post_id"];
        $this->author_user_id = $val["author_user_id"];
        $this->author_username = $val["author_username"];
        $this->author_fullname = $val["author_fullname"];
        $this->author_avatar = $val["author_avatar"];
        $this->post_text = $val["post_text"];
        $this->source = $val["source"];
        $this->location = $val["location"];
        $this->place = $val["place"];
        $this->geo = $val["geo"];
        $this->pub_date = $val["pub_date"];
        $this->adj_pub_date = $val["adj_pub_date"];
        $this->in_reply_to_user_id = $val["in_reply_to_user_id"];
        $this->in_reply_to_post_id = $val["in_reply_to_post_id"];
        $this->reply_count_cache = $val["reply_count_cache"];
        $this->in_retweet_of_post_id = $val["in_retweet_of_post_id"];
        $this->retweet_count_cache = $val["retweet_count_cache"];
        $this->reply_retweet_distance = $val["reply_retweet_distance"];
        $this->is_geo_encoded = $val["is_geo_encoded"];
        $this->network = $val["network"];
        $this->is_reply_by_friend = PDODAO::convertDBToBool($val["is_reply_by_friend"]);
        $this->is_retweet_by_friend = PDODAO::convertDBToBool($val["is_retweet_by_friend"]);
    }

    /**
     * Extract URLs from post text
     * @param string $post_text
     * @return array $matches
     */
    public static function extractURLs($post_text) {
        preg_match_all('!https?://[\S]+!', $post_text, $matches);
        return $matches[0];
    }
}