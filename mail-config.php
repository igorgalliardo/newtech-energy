<?php

return [
    'host' => getenv('NTE_SMTP_HOST') ?: 'mail.galltech.com.br',
    'port' => (int) (getenv('NTE_SMTP_PORT') ?: 465),
    'secure' => getenv('NTE_SMTP_SECURE') ?: 'ssl',
    'username' => getenv('NTE_SMTP_USERNAME') ?: '',
    'password' => getenv('NTE_SMTP_PASSWORD') ?: '',
    'from_email' => getenv('NTE_SMTP_FROM_EMAIL') ?: 'contato@galltech.com.br',
    'from_name' => getenv('NTE_SMTP_FROM_NAME') ?: 'NewTech Energy',
    'to_email' => getenv('NTE_CONTACT_TO_EMAIL') ?: 'contato@galltech.com.br',
    'to_name' => getenv('NTE_CONTACT_TO_NAME') ?: 'NewTech Energy',
];
