<?php

class Tracker
{
    private $connection;
    private $APIEndpoint = "https://track.amazon.it/api/tracker/";

    //Got from https://track.amazon.it/getLocalizedStrings
    private $labels = [
        "swa_rex_detail_pickedUp"                   => "Pacco ritirato",
        "swa_rex_arrived_at_sort_center"            => "Il pacco è arrivato presso la sede del corriere",
        "swa_rex_ofd"                               => "In consegna",
        "swa_rex_intransit"                         => "In transito",
        "swa_rex_detail_departed"                   => "Il pacco ha lasciato la sede del corriere",
        "swa_rex_detail_creation_confirmed"         => "Etichetta creata",
        "swa_rex_detail_arrived_at_delivery_Center" => "In consegna",
        "swa_rex_detail_delivered"                  => "Pacco consegnato",
        "swa_rex_detail_delivery_attempted_1"       => "Tentativo di consegna effettuato",
    ];

    public function __construct()
    {
        $this->connection = curl_init();

        if ($this->connection === false) {
            throw new Exception("Cannot open a valid connection.", 500);
        }

        curl_setopt($this->connection, CURLOPT_HEADER, false);
        curl_setopt($this->connection, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->connection, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($this->connection, CURLOPT_CUSTOMREQUEST, 'GET');
    }

    public function close(): void
    {
        curl_close($this->connection);
    }

    public function getTrackingHistory(String $trackingCode): String
    {
        if (!$trackingCode || strlen(trim($trackingCode)) == 0) {
            throw new Exception("The tracking code is blank.", 400);
        }

        curl_setopt($this->connection, CURLOPT_URL, $this->APIEndpoint . $trackingCode);

        $data = $this->getData();

        return json_encode([
            "tracking-code" => $trackingCode,
            "history"       => $data,
        ]);
    }

    private function getData(): array
    {
        $data = json_decode(curl_exec($this->connection));

        $code = curl_getinfo($this->connection, CURLINFO_HTTP_CODE);
        if ($code != 200) {
            throw new Exception("Generic Error.", $code);
        }

        if ($data == null || !is_object($data)) {
            throw new Exception("Tracking history not found.", 422);
        }

        if (!isset($data->eventHistory) || strlen(trim($data->eventHistory)) == 0) {
            throw new Exception("Tracking history not found.", 422);
        }

        $history = json_decode($data->eventHistory);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Cannot read the tracking history.", 422);
        }

        if (!is_object($history) || !isset($history->eventHistory)) {
            throw new Exception("Cannot read the event history.", 422);
        }

        return $this->formatHistory($history->eventHistory);
    }

    private function formatHistory(array $history): array
    {
        $outputData = [];

        foreach ($history as $event) {
            array_push($outputData, $this->getFormattedEvent($event));
        }

        return $outputData;
    }

    private function getFormattedEvent(object $event): array
    {
        if ($event == null || !is_object($event)) {
            throw new Exception("Error during the history processing.", 422);
        }

        if (!isset($event->statusSummary) || !isset($event->statusSummary->localisedStringId) || !isset($event->eventTime)) {
            throw new Exception("Error during the history processing.", 422);
        }

        return [
            "state"    => $this->getStatusLabel($event->statusSummary->localisedStringId),
            "time"     => date("d/m/Y H:i:s", strtotime($event->eventTime)),
            "location" => $this->getLocationDetails($event->location),
        ];
    }

    private function getLocationDetails(?Object $location): ?String
    {
        $ret = [];

        if ($location == null || !is_object($location)) {
            return null;
        }

        if (isset($location->city)) {
            array_push($ret, $location->city);
        }
        if (isset($location->stateProvince)) {
            array_push($ret, $location->stateProvince);
        }
        if (isset($location->countryCode)) {
            array_push($ret, $location->countryCode);
        }

        return count($ret) > 0 ? implode(", ", $ret) : null;
    }

    private function getStatusLabel(String $code): String
    {
        if (!$code || strlen(trim($code)) == 0) {
            return "";
        }

        if (!isset($this->labels[$code])) {
            return $code;
        }

        return $this->labels[$code];
    }
}
