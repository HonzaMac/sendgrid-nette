# sendgrid-nette
Sendgrid integration for Nette mailer

## Install
```
composer require istrix/sendgrid-nette
```

## Configuration
In config add:

```
parameters:
	sendgrid:
		key: 'yourkey'

services:
	nette.mailer: Istrix\Mail\SendgridMailer(%sendgrid.key%, %tempDir%)
```

## Usage
Just inject IMailer and send message...

```php
	/** @var IMailer @inject */
	public $mailer;
	
	protected function sendMail() {
		...
		$this->mailer->send($message);
		...
	}
	
```
