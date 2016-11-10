<?php namespace MauticPlugin\PipedriveBundle\Benhawker\Pipedrive\Library;

use MauticPlugin\PipedriveBundle\Benhawker\Pipedrive\Exceptions\PipedriveMissingFieldError;
use MauticPlugin\PipedriveBundle\Benhawker\Pipedrive\Pipedrive;
/**
 * Pipedrive Activities Methods
 *
 * Activities are appointments/tasks/events on a calendar that can be
 * associated with a Deal, a Person and an Organization. Activities can
 * be of different type (such as call, meeting, lunch or a custom type
 * - see ActivityTypes object) and can be assigned to a particular User.
 * Note that activities can also be created without a specific date/time.
 *
 */
class Activities
{
    /**
     * Hold the pipedrive cURL session
     * @var Curl Object
     */
    protected $curl;

    /**
     * Initialise the object load master class
     */
    public function __construct($master)
    {
        //associate curl class
        $this->curl = $master->curl();
    }

    /**
     * Adds a activity
     *
     * @param  array $data activity detials
     * @return array returns detials of the activity
     */
    public function add($data)
    {

        //if there is no subject or type set chuck error as both of the fields are required
        if (!isset($data['subject']) or !isset($data['type'])) {
            throw new PipedriveMissingFieldError('You must include both a "subject" and "type" field when inserting a note');
        }

        return $this->curl->post('activities', $data);
    }
}
