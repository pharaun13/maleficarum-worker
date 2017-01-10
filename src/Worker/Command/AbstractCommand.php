<?php
/**
 * This class provides a basis for all other worker command classes
 *
 * @abstract
 */

namespace Maleficarum\Worker\Command;

abstract class AbstractCommand
{
    /**
     * Definition of the parent handler id.
     */
    const DATA_KEY_PARENT_HANDLER_ID = '__parentHandlerId';

    /**
     * Internal storage for command data.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Initialize a new command object.
     */
    public function __construct() {
        $this->data['__type'] = $this->getType();
    }

    /**
     * Set the parent handler ID.
     *
     * @param string $id
     *
     * @return \Maleficarum\Worker\Command\AbstractCommand
     */
    public function setParentHandlerId($id) {
        $this->data[self::DATA_KEY_PARENT_HANDLER_ID] = $id;

        return $this;
    }

    /**
     * Fetch the current parent handler id.
     *
     * @return string
     */
    public function getParentHandlerId() {
        return !empty($this->data[self::DATA_KEY_PARENT_HANDLER_ID]) ? $this->data[self::DATA_KEY_PARENT_HANDLER_ID] : '';
    }

    /**
     * Created for logging command parameters
     *
     * @return string
     */
    public function __toString() {
        $toStringArray = $this->data; //copy data
        unset($toStringArray['__type']); //not needed in logs
        unset($toStringArray[self::DATA_KEY_PARENT_HANDLER_ID]); //not needed in logs
        return json_encode($toStringArray);
    }

    /**
     * Fetch current command data in the form of a serialized JSON string - this is sent to the queue broker.
     *
     * @throws \RuntimeException
     * @return string
     */
    public function toJSON() {
        if (!$this->validate()) {
            throw new \RuntimeException('Attempting to serialize an incomplete command object. \Maleficarum\Worker\Command\AbstractCommand');
        }

        return json_encode($this->data);
    }

    /**
     * Unserialize this command data based on the provided JSON string.
     *
     * @param string $json
     *
     * @return \Maleficarum\Worker\Command\AbstractCommand
     */
    public function fromJSON($json) {
        $this->data = json_decode($json, true);
        is_array($this->data) or $this->data = ['__type' => $this->getType()];

        return $this;
    }

    /**
     * Create a command object based on the provided JSON data.
     *
     * @param string $json
     *
     * @throws \InvalidArgumentException
     * @return \Maleficarum\Worker\Command\AbstractCommand|boolean
     */
    static public function decode($json) {
        $data = json_decode($json, true);

        // not a JSON structure
        if (!is_array($data)) throw new \InvalidArgumentException('Incorrect command received - not a proper JSON. \Maleficarum\Worker\Command\AbstractCommand::decode()');

        // not a command
        if (!array_key_exists('__type', $data)) return false;

        // not a supported command (no command object or no handler)
        if (!class_exists('\Command\\' . $data['__type'], true)) return false;
        if (!class_exists('\Handler\\' . $data['__type'], true)) return false;

        $cmd = \Maleficarum\Ioc\Container::get('Command\\' . $data['__type'])->fromJson($json);

        return $cmd;
    }

    /**
     * Validate current state of the $data property to check if it can be considered a complete command.
     *
     * @abstract
     * @return bool
     */
    abstract public function validate();

    /**
     * Fetch the type of current command. This is used to distinguish which handler to use for this command (on the worker side).
     *
     * @return String
     */
    abstract public function getType();
}
