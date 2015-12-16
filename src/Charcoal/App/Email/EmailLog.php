<?php

namespace Charcoal\App\Email;

// Dependencies from `PHP`
use \DateTime;
use \DateTimeInterface;
use \Exception;
use \InvalidArgumentException;

// From `charcoal-core`
use \Charcoal\Core\IndexableInterface;
use \Charcoal\Core\IndexableTrait;
use \Charcoal\Model\AbstractModel;

/**
 * Email log
 */
class EmailLog extends AbstractModel implements IndexableInterface
{
    use IndexableTrait;

    /**
     * Type of log (ex: email)
     * @var string $type
     */
    private $type;

    /**
     * Action logged (ex: send)
     * @var string $action
     */
    private $action;

    /**
     * @var mixed $raw_response
     */
    private $raw_response;

    /**
     * Unique message identifier
     * @var string $message_id
     */
    private $message_id;

    /**
     * @var string $campaign
     */
    private $campaign;

    /**
     * @var string $from
     */
    private $from;

    /**
     * @var string $to
     */
    private $to;

    /**
     * @var string $subject
     */
    private $subject;

    /**
     * Error code (0 = success)
     * @var int $_send_status
     */
    private $send_status;

    /**
     * Error message
     * @var string $_send_error
     */
    private $send_error;

    /**
     * @var DateTime $_send_ts
     */
    private $send_ts;

    /**
     * @var string $ip
     */
    private $ip;
    /**
     * @var string $session_id
     */
    private $session_id;

    /**
     * @return string
     */
    public function key()
    {
        return 'id';
    }

    /**
     * @param string $type The log action type. (ex: "email").
     * @throws InvalidArgumentException If the type is not a string.
     * @return EmailLog Chainable
     */
    public function set_type($type)
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException(
                'Log type must be a string.'
            );
        }
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function type()
    {
        return $this->type;
    }

    /**
     * @param string $action The log action (ex: "send").
     * @throws InvalidArgumentException If the action is not a string.
     * @return EmailLog Chainable
     */
    public function set_action($action)
    {
        if (!is_string($action)) {
            throw new InvalidArgumentException(
                'Action must be a string.'
            );
        }
        $this->action = $action;
        return $this;
    }

    /**
     * @return string
     */
    public function action()
    {
        return $this->action;
    }

    /**
     * @param mixed $res The mailer response object / array.
     * @return EmailLog Chainable
     */
    public function set_raw_response($res)
    {
        $this->raw_response = $res;
        return $this;
    }

    /**
     * @return mixed
     */
    public function raw_response()
    {
        return $this->raw_response;
    }

    /**
     * @param string $message_id The SMTP message ID.
     * @throws InvalidArgumentException If the message id is not a string.
     * @return EmailLog Chainable
     */
    public function set_message_id($message_id)
    {
        if (!is_string($message_id)) {
            throw new InvalidArgumentException(
                'Message ID must be a string.'
            );
        }
        $this->message_id = $message_id;
        return $this;
    }

    /**
     * @return string
     */
    public function message_id()
    {
        return $this->message_id;
    }


    /**
     * @param string $campaign The campaign identifier.
     * @throws InvalidArgumentException If the campaign parameter is invalid.
     * @return EmailInterface Chainable
     */
    public function set_campaign($campaign)
    {
        if (!is_string($campaign)) {
            throw new InvalidArgumentException(
                'Campaign must be a string'
            );
        }
        $this->campaign = $campaign;
        return $this;
    }

    /**
     * Get the campaign identifier.
     *
     * If it has not been explicitely set, it will be aut-generated (with uniqid).
     *
     * @return string
     */
    public function campaign()
    {
        return $this->campaign;
    }

    /**
     * @param string $from The sender email address.
     * @throws InvalidArgumentException If the email is not a string.
     * @return EmailLog Chainable
     */
    public function set_from($from)
    {
        if (!is_string($from)) {
            throw new InvalidArgumentException(
                'From (sender email) must be a string.'
            );
        }
        $this->from = $from;
        return $this;
    }

    /**
     * @return string
     */
    public function from()
    {
        return $this->from;
    }

    /**
     * @param string $to The recipient email address.
     * @throws InvalidArgumentException If the email is not a string.
     * @return EmailLog Chainable
     */
    public function set_to($to)
    {
        if (!is_string($to)) {
            throw new InvalidArgumentException(
                'To (recipient email) must be a string.'
            );
        }
        $this->to = $to;
        return $this;
    }

    /**
     * @return string
     */
    public function to()
    {
        return $this->to;
    }

    /**
     * @param string $subject The email subject.
     * @return EmailLog Chainable
     */
    public function set_subject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return string
     */
    public function subject()
    {
        return $this->subject;
    }

    /**
     * @param string|DateTime|null $send_ts The "send date" datetime value.
     * @throws InvalidArgumentException If the ts is not a valid datetime value.
     * @return EmailLog Chainable
     */
    public function set_send_ts($send_ts)
    {
        if ($send_ts === null) {
            $this->send_ts = null;
            return $this;
        }
        if (is_string($send_ts)) {
            try {
                $send_ts = new DateTime($send_ts);
            } catch (Exception $e) {
                throw new InvalidArgumentException($e->getMessage());
            }
        }
        if (!($send_ts instanceof DateTimeInterface)) {
            throw new InvalidArgumentException(
                'Invalid "Send Date" value. Must be a date/time string or a DateTime object.'
            );
        }
        $this->send_ts = $send_ts;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function send_ts()
    {
        return $this->send_ts;
    }

    /**
     * @param mixed $ip The IP adress.
     * @return EmailLog Chainable
     */
    public function set_ip($ip)
    {
        $this->ip = $ip;
        return $this;
    }

    /**
     * @return mixed
     */
    public function ip()
    {
        return $this->ip;
    }

    /**
     * @param string $session_id The session identifier.
     * @return EmailLog Chainable
     */
    public function set_session_id($session_id)
    {
        $this->session_id = $session_id;
        return $this;
    }

    /**
     * @return string
     */
    public function session_id()
    {
        return $this->session_id;
    }

    /**
     * StorableTrait > pre_save()
     *
     * @return void
     */
    public function pre_save()
    {
        parent::pre_save();
        $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        $session_id = session_id();

        $this->set_ip($ip);
        $this->set_session_id($session_id);

    }
}