<?php

/**
 * This code was generated by
 * \ / _    _  _|   _  _
 * | (_)\/(_)(_|\/| |(/_  v1.0.0
 * /       /
 */

namespace Twilio\Rest\Insights\V1;

use Twilio\Options;
use Twilio\Values;

abstract class ConferenceOptions {
    /**
     * @param string $conferenceSid The conference_sid
     * @param string $friendlyName The friendly_name
     * @param string $status The status
     * @param string $createdAfter The created_after
     * @param string $createdBefore The created_before
     * @param string $mixerRegion The mixer_region
     * @param string $tags The tags
     * @param string $subaccount The subaccount
     * @param string $detectedIssues The detected_issues
     * @param string $endReason The end_reason
     * @return ReadConferenceOptions Options builder
     */
    public static function read(string $conferenceSid = Values::NONE, string $friendlyName = Values::NONE, string $status = Values::NONE, string $createdAfter = Values::NONE, string $createdBefore = Values::NONE, string $mixerRegion = Values::NONE, string $tags = Values::NONE, string $subaccount = Values::NONE, string $detectedIssues = Values::NONE, string $endReason = Values::NONE): ReadConferenceOptions {
        return new ReadConferenceOptions($conferenceSid, $friendlyName, $status, $createdAfter, $createdBefore, $mixerRegion, $tags, $subaccount, $detectedIssues, $endReason);
    }
}

class ReadConferenceOptions extends Options {
    /**
     * @param string $conferenceSid The conference_sid
     * @param string $friendlyName The friendly_name
     * @param string $status The status
     * @param string $createdAfter The created_after
     * @param string $createdBefore The created_before
     * @param string $mixerRegion The mixer_region
     * @param string $tags The tags
     * @param string $subaccount The subaccount
     * @param string $detectedIssues The detected_issues
     * @param string $endReason The end_reason
     */
    public function __construct(string $conferenceSid = Values::NONE, string $friendlyName = Values::NONE, string $status = Values::NONE, string $createdAfter = Values::NONE, string $createdBefore = Values::NONE, string $mixerRegion = Values::NONE, string $tags = Values::NONE, string $subaccount = Values::NONE, string $detectedIssues = Values::NONE, string $endReason = Values::NONE) {
        $this->options['conferenceSid'] = $conferenceSid;
        $this->options['friendlyName'] = $friendlyName;
        $this->options['status'] = $status;
        $this->options['createdAfter'] = $createdAfter;
        $this->options['createdBefore'] = $createdBefore;
        $this->options['mixerRegion'] = $mixerRegion;
        $this->options['tags'] = $tags;
        $this->options['subaccount'] = $subaccount;
        $this->options['detectedIssues'] = $detectedIssues;
        $this->options['endReason'] = $endReason;
    }

    /**
     * The conference_sid
     *
     * @param string $conferenceSid The conference_sid
     * @return $this Fluent Builder
     */
    public function setConferenceSid(string $conferenceSid): self {
        $this->options['conferenceSid'] = $conferenceSid;
        return $this;
    }

    /**
     * The friendly_name
     *
     * @param string $friendlyName The friendly_name
     * @return $this Fluent Builder
     */
    public function setFriendlyName(string $friendlyName): self {
        $this->options['friendlyName'] = $friendlyName;
        return $this;
    }

    /**
     * The status
     *
     * @param string $status The status
     * @return $this Fluent Builder
     */
    public function setStatus(string $status): self {
        $this->options['status'] = $status;
        return $this;
    }

    /**
     * The created_after
     *
     * @param string $createdAfter The created_after
     * @return $this Fluent Builder
     */
    public function setCreatedAfter(string $createdAfter): self {
        $this->options['createdAfter'] = $createdAfter;
        return $this;
    }

    /**
     * The created_before
     *
     * @param string $createdBefore The created_before
     * @return $this Fluent Builder
     */
    public function setCreatedBefore(string $createdBefore): self {
        $this->options['createdBefore'] = $createdBefore;
        return $this;
    }

    /**
     * The mixer_region
     *
     * @param string $mixerRegion The mixer_region
     * @return $this Fluent Builder
     */
    public function setMixerRegion(string $mixerRegion): self {
        $this->options['mixerRegion'] = $mixerRegion;
        return $this;
    }

    /**
     * The tags
     *
     * @param string $tags The tags
     * @return $this Fluent Builder
     */
    public function setTags(string $tags): self {
        $this->options['tags'] = $tags;
        return $this;
    }

    /**
     * The subaccount
     *
     * @param string $subaccount The subaccount
     * @return $this Fluent Builder
     */
    public function setSubaccount(string $subaccount): self {
        $this->options['subaccount'] = $subaccount;
        return $this;
    }

    /**
     * The detected_issues
     *
     * @param string $detectedIssues The detected_issues
     * @return $this Fluent Builder
     */
    public function setDetectedIssues(string $detectedIssues): self {
        $this->options['detectedIssues'] = $detectedIssues;
        return $this;
    }

    /**
     * The end_reason
     *
     * @param string $endReason The end_reason
     * @return $this Fluent Builder
     */
    public function setEndReason(string $endReason): self {
        $this->options['endReason'] = $endReason;
        return $this;
    }

    /**
     * Provide a friendly representation
     *
     * @return string Machine friendly representation
     */
    public function __toString(): string {
        $options = \http_build_query(Values::of($this->options), '', ' ');
        return '[Twilio.Insights.V1.ReadConferenceOptions ' . $options . ']';
    }
}