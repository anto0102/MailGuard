import Extend from 'flarum/common/extenders';
import app from 'flarum/admin/app';

export default [
  new Extend.Admin()
    .setting(
      () => ({
        setting: 'anto0102-mailguard.mode',
        label: app.translator.trans('anto0102-mailguard.admin.mode_label', {}, true),
        help: app.translator.trans('anto0102-mailguard.admin.mode_help', {}, true),
        type: 'select',
        options: {
          allow: 'Allowlist (only listed domains allowed)',
          deny: 'Denylist (listed domains blocked)',
        },
        default: 'allow',
      }),
      100
    )
    .setting(
      () => ({
        setting: 'anto0102-mailguard.domains',
        label: app.translator.trans('anto0102-mailguard.admin.domains_label', {}, true),
        help: app.translator.trans('anto0102-mailguard.admin.domains_help', {}, true),
        type: 'textarea',
        placeholder: 'example.com\notherdomain.org',
      }),
      90
    )
    .setting(
      () => ({
        setting: 'anto0102-mailguard.message',
        label: app.translator.trans('anto0102-mailguard.admin.message_label', {}, true),
        help: app.translator.trans('anto0102-mailguard.admin.message_help', {}, true),
        type: 'text',
        placeholder: 'Registration with this email domain is not allowed.',
      }),
      80
    )
    .setting(
      () => ({
        setting: 'anto0102-mailguard.check_mx',
        label: app.translator.trans('anto0102-mailguard.admin.check_mx_label', {}, true),
        help: app.translator.trans('anto0102-mailguard.admin.check_mx_help', {}, true),
        type: 'boolean',
      }),
      70
    )
    .setting(
      () => ({
        setting: 'anto0102-mailguard.sanitize_aliases',
        label: 'Enable Alias Anti-Fraud (Block + and . tricks)',
        help: 'Prevent users from registering infinite accounts using Gmail aliases (e.g. john.doe+spam@gmail).',
        type: 'boolean',
      }),
      60
    )
    .setting(
      () => ({
        setting: 'anto0102-mailguard.alias_action',
        label: 'Alias Handling Action',
        help: 'Choose how to treat caught aliases. Strict Block rejects them immediately. Silent Clean converts them to their raw format inside the database to naturally prevent duplicates.',
        type: 'select',
        options: {
          block: 'Strict Block (Show Error)',
          sanitize: 'Silent Clean (Sanitize and save)',
        },
        default: 'block',
      }),
      50
    )
    .setting(
      () => ({
        setting: 'anto0102-mailguard.sanitize_providers',
        label: 'Target Alias Providers',
        help: 'One per line. Domains to apply alias rules to.',
        type: 'textarea',
        placeholder: 'gmail.com\ngooglemail.com',
      }),
      40
    )
    .setting(
      () => ({
        setting: 'anto0102-mailguard.alias_message',
        label: 'Alias Block Message',
        help: 'Error message to show when a user is blocked for using an alias.',
        type: 'text',
        placeholder: 'Alias emails (+ or dots) are not allowed.',
      }),
      30
    ),
];
