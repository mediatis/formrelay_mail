# Introduction

EXT:formrelay_mail is providing a destination for EXT:formrelay, which handles form submissions for various endpoints.

It is dispatching the form data as standardised email.

# Setup

All basic settings, explained in EXT:formrelay, (including the overwrite mechanics) apply to this extension as well.  

## plugin.tx_formrelay_mail.settings.enabled

Default: `0`.

Set to `1` to enable the extension.

## plugin.tx_formrelay_mail.settings.recipients

Default: `user@domain.com, user2@domain.com`.

Set the email addresses of the recipients as comma separated list.

## plugin.tx_formrelay_mail.settings.sender

Default: `website@domain.com`.

Set the email address of the sender.

## plugin.tx_formrelay_mail.settings.subject 

Default: `Mail from website`.

Set the subject of the email.

## plugin.tx_formrelay_mail.settings.includeAttachmentsInMail

Default: `0`.

Set to `1` if uploaded files shall be attached to the email.  
In any case the uploaded files will be linked within the email (as part of the form data).

## plugin.tx_formrelay_mail.settings.signMessageBody

Default: `0`.

Set to `1` if the message body should be signed with an S/MIME certificate and key.
plugin.tx_formrelay_mail.settings.signingCertificate and plugin.tx_formrelay_mail.settings.signingCertificate.signingPrivateKey need to be set to filepaths to valid .crt and .key files.

## plugin.tx_formrelay_mail.settings.encryptMessageBody

Default: `0`.

Set to `1` if the message body should be encrypted with an S/MIME certificate.
plugin.tx_formrelay_mail.settings.encryptionCertificate needs to be set to a filepath to a valid .crt file.

## plugin.tx_formrelay_mail.settings.valueDelimiter

Default: `\s=\s`.

Set the separator between field name and value.  
`\s` will be translated to a space character.  
`\n` will be translated to a line break.

## plugin.tx_formrelay_mail.settings.lineDelimiter

Default: `\n`.

Set the separator between the different field-name-and-value-pairs.  
`\s` will be translated to a space character.  
`\n` will be translated to a line break.

## Generating changelog
- CHANGELOG.md must be generated together with every git release and commited together with ext_emconf.php.  
`printf '%s\n%s\n' "$(git log $(git describe --tags --abbrev=0)..HEAD --no-merges --format=%B)" "$(cat CHANGELOG.txt)" > CHANGELOG.txt`
