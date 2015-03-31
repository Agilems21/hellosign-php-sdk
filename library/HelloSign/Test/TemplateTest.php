<?php

namespace HelloSign\Test;

/**
 * 
 * You must have created a template manually prior to running this test suite 
 * @author Steve Gough
 *
 */
class TemplateTest extends AbstractTest
{
    /**
     * @group read
     * @expectedException HelloSign\Error
     * @expectedExceptionMessage Page not found
     */
    public function testGetTemplatesWithPageNotFound()
    {
        $templates = $this->client->getTemplates(9999);
    }

    /**
     * @group read
     */
    public function testGetTemplates()
    {
        $templates = $this->client->getTemplates();
        $template = $templates[0];

        $template2 = $this->client->getTemplate($template->getId());


        $this->assertInstanceOf('HelloSign\TemplateList', $templates);
        $this->assertGreaterThan(0, count($templates));

        $this->assertInstanceOf('HelloSign\Template', $template);
        $this->assertNotNull($template->getId());

        $this->assertInstanceOf('HelloSign\Template', $template2);
        $this->assertNotNull($template2->getId());

        $this->assertEquals($template, $template2);


        return $template;
    }

    /**
     * @depends testGetTemplates
     * @group update
     */
    public function testAddTemplateUser($template)
    {
    	$response = $this->client->inviteTeamMember($this->team_member_2);
        $response = $this->client->addTemplateUser($template->getId(), $this->team_member_2);

        $this->assertInstanceOf('HelloSign\Template', $response);
        $has_template = false;
		foreach($response->getAccounts() as $account) {
			if($account->email_address == $this->team_member_2 || $account->account_id == $this->team_member_2 ) {
				$has_template = true;
			}
		}
        
        $this->isTrue($has_template);
        return array($template, $this->team_member_2);
    }

    /**
     * @depends testAddTemplateUser
     * @group update
     */
    public function testRemoveTemplateUser($template_and_member)
    {
    	$template = $template_and_member[0];
    	$member = $template_and_member[1];
        $response = $this->client->removeTemplateUser($template->getId(), $member);

        $this->assertInstanceOf('HelloSign\Template', $response);
        
    	$has_template = false;
		foreach($response->getAccounts() as $account) {
			if($account->email_address == $member || $account->account_id == $member ) {
				$has_template = true;
			}
		}
        $this->isFalse($has_template);
    }

    /**
     * @group embedded
     */
    public function testCreateEmbeddedDraft() 
    {
        $client_id = $_ENV['CLIENT_ID'];


        $request = new \HelloSign\Template();
        $request->enableTestMode();
        $request->setClientId($client_id);
        $request->addFile(__DIR__ . '/nda.docx');
        $request->setTitle('Test Title');
        $request->setSubject('Test Subject');
        $request->setMessage('Test Message');
        $request->addSignerRole('Test Role', 1);
        $request->addSignerRole('Test Role 2', 2);
        $request->addCCRole('Test CC Role');
        $request->addMergeField('Test Merge', 'text');
        $request->addMergeField('Test Merge 2', 'checkbox');

        $return = $this->client->createEmbeddedDraft($request);

        $this->assertTrue(is_string($return->getId()));
        $this->assertTrue(is_string($return->getEditUrl()));
        $this->assertTrue($return->isEmbeddedDraft());

        return $return->getId();
    }

    /**
     * @depends testCreateEmbeddedDraft
     * @group embedded
     */
    // public function testGetEmbeddedEditUrl($templateId) 
    // {

    //     $res = $this->client->getEmbeddedEditUrl($templateId);

    //     print_r($res);

    //     $this->assertTrue($res);
    // }

    /**
     * @group embedded
     */
    public function testCreateUnclaimedDraftEmbeddedWithTemplate() 
    {

        $client_id = $_ENV['CLIENT_ID'];
        // $template = $_ENV['TEMPLATE_ID'];
        $templateId = 'e9bbed302ee26f34e2134d1a9f965f5bbc3dbfa3';

        $baseReq = new \HelloSign\TemplateSignatureRequest();
        $baseReq->setTemplateId($templateId);
        $baseReq->setCC('Manager','dumbledore@hogwarts.edu');
        $baseReq->setSigner('New Student', 'harry@potter.net', 'Harry Potter');
        // $baseReq->setCustomFieldValue('Owl', 'Hedwig');
        $baseReq->setSigningRedirectUrl('http://hogwarts.edu/success');
        $baseReq->setRequestingRedirectUrl('http://hogwarts.edu');
        $baseReq->setRequesterEmailAddress('herman@hogwarts.com');
        $baseReq->addMetadata('House', 'Griffyndor');

        $request = new \HelloSign\EmbeddedSignatureRequest($baseReq);
        $request->setClientId($client_id);
        $request->enableTestMode();
        $request->setEmbeddedSigning();

        print_r($request->toParams());

        $response = $this->client->createUnclaimedDraftEmbeddedWithTemplate($request);
    }

}
