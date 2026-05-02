import Extend from 'flarum/common/extenders';
import app from 'flarum/admin/app';

export default [
  new Extend.Admin()
    .setting(
      () => ({
        setting: 'anto0102-mailguard.mode',
        label: app.translator.trans('anto0102-mailguard.admin.mode_label', {}, true),
        help: 'Choose "Allowlist" to only allow specific domains or "Denylist" to block specific spam providers.',
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
        help: 'Verify if the domain exists and can receive mail. Blocks fakes like @nonexistent.web.',
        type: 'boolean',
      }),
      70
    )
    .setting(
      () => ({
        setting: 'anto0102-mailguard.catch_plus_aliases',
        label: 'Enable "+" Alias Anti-Fraud',
        help: 'Prevents "infinite accounts" like user+1@gmail.com. Requires setting the Action below.',
        type: 'boolean',
      }),
      65
    )
    .setting(
      () => ({
        setting: 'anto0102-mailguard.catch_dot_aliases',
        label: 'Gmail Dot Strategy',
        help: 'Select how to handle dots (es. m.a.r.i.o@gmail.com). See README for details on each strategy.',
        type: 'select',
        options: {
          none: 'Disabled (Standard - Allow dots)',
          check: 'Check for Duplicates (Recommended - Anti-Clone)',
          block: 'Strict Block (Reject any dots)',
          sanitize: 'Silent Clean (Remove dots & Save)',
        },
        default: 'none',
      }),
      60
    )
    .setting(
      () => ({
        setting: 'anto0102-mailguard.alias_action',
        label: 'Global Alias Action (for Plus and Sanitize)',
        help: 'Action to take when a Plus alias or Dot "Silent Clean" is detected.',
        type: 'select',
        options: {
          block: 'Strict Block (Show Error)',
          sanitize: 'Silent Clean (Sanitize and save)',
        },
        default: 'block',
      }),
      55
    )
    .setting(
      () => ({
        setting: 'anto0102-mailguard.alias_message',
        label: 'Plus Alias Error Message',
        type: 'text',
        placeholder: 'Alias emails (+) are not allowed.',
      }),
      50
    )
    .setting(
      () => ({
        setting: 'anto0102-mailguard.dot_message',
        label: 'Strict Dot Error Message',
        type: 'text',
        placeholder: 'Dots (.) are not allowed in the email address.',
      }),
      45
    )
    .setting(
      () => ({
        setting: 'anto0102-mailguard.clone_message',
        label: 'Clone/Duplicate Detection Message',
        type: 'text',
        placeholder: 'An account with a similar email (alias) already exists.',
      }),
      40
    )
    .setting(
      () => ({
        setting: 'anto0102-mailguard.sanitize_providers',
        label: 'Target Alias Providers',
        help: 'Domains to apply alias rules to (e.g. gmail.com).',
        type: 'textarea',
        placeholder: 'gmail.com\ngooglemail.com',
      }),
      30
    ),
];
