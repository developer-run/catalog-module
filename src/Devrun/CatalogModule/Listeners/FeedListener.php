<?php


namespace Devrun\CatalogModule\Listeners;

use Devrun\CmsModule\CatalogModule\Facades\FeedFacade;
use Kdyby\Events\Subscriber;
use Nette;
use Nette\Mail\IMailer;
use Nette\Mail\Message;

class FeedListener implements Subscriber
{

    /** @var IMailer */
    private $mailer;

    /** @var Nette\Application\LinkGenerator */
    private $linkGenerator;

    /** @var Nette\Bridges\ApplicationLatte\Template */
    private $templateFactory;

    /** @var bool */
    private $emailSend = true;


    /**
     * FeedListener constructor.
     * @param $emailSend
     * @param IMailer $mailer
     * @param Nette\Application\LinkGenerator $linkGenerator
     * @param Nette\Application\UI\ITemplateFactory $templateFactory
     */
    public function __construct($emailSend, IMailer $mailer, Nette\Application\LinkGenerator $linkGenerator, Nette\Application\UI\ITemplateFactory $templateFactory)
    {
        $this->mailer          = $mailer;
        $this->emailSend       = $emailSend;
        $this->linkGenerator   = $linkGenerator;
        $this->templateFactory = $templateFactory;
    }


    public function onUpdate(FeedFacade $class, array $toNew, array $toUpdate, array $toRemove)
    {
        $template = $this->createTemplate();
        $template->setFile(__DIR__ . '/email.latte');
        $template->time    = date("j. n. Y H:i:s");
        $template->news    = $toNew;
        $template->updated = $toUpdate;
        $template->removed = $toRemove;
        $template->lines   = max(count($toNew), count($toUpdate), count($toRemove));

        $mail = new Message();
        $mail->setFrom('Franta Update <ulozdo@info.cz>')
//             ->setHtmlBody($latte->renderToString($template, $params));
             ->setHtmlBody($template);

        $this->mailer->send($mail);
    }


    /**
     * @return Nette\Application\UI\ITemplate
     */
    protected function createTemplate(): Nette\Application\UI\ITemplate
    {
        $template = $this->templateFactory->createTemplate();
        $template->getLatte()->addProvider('uiControl', $this->linkGenerator);

        return $template;
    }


    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return $this->emailSend
            ? [FeedFacade::FEED_EVENT]
            : [];
    }
}