<?php

class Helloday_Mail_Model_Api_Template extends Helloday_Mail_Model_Api_Abstract
{
    /**
     * Create Template.
     *
     * @param string $html
     *
     * @return string
     */
    public function create($html, $templateName)
    {
        $mailChimp = $this->getClient();
        $mailChimp->post("templates", [
            'name' => $templateName,
            'html' => $html,
        ]);

        $response = $mailChimp->getLastResponse();
        $responseObj = json_decode($response['body']);
        return $responseObj->id;
    }

    /**
     * Edit Mailchimp template.
     *
     * @param string $id
     * @param string $html
     *
     * @return mixed
     */
    public function edit($id, $html)
    {
        $mailchimp = $this->getClient();
        $mailchimp->patch(
            '/templates/' . $id,
            ['html' => $html]
        );
        $response = $mailchimp->getLastResponse();
        $responseObj = json_decode($response['body']);
        return $responseObj->id;
    }

    /**
     * Get Template/templates
     *
     * @param $id
     *
     * @return array
     */
    public function read($id = null)
    {
        $mailChimp = $this->getClient();
        $method = $id ? "/templates/" . $id : "/templates/";
        $mailChimp->get($method);
        $response = $mailChimp->getLastResponse();
        $responseObj = json_decode($response['body']);
        return $responseObj;
    }
}