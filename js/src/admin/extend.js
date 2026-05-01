import Extend from 'flarum/common/extenders';
import app from 'flarum/admin/app';

export default [
  new Extend.Admin()
    .setting(
      () => ({
        setting: 'anto0102-sanized.mode',
        label: app.translator.trans('anto0102-sanized.admin.mode_label', {}, true),
        help: app.translator.trans('anto0102-sanized.admin.mode_help', {}, true),
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
        setting: 'anto0102-sanized.domains',
        label: app.translator.trans('anto0102-sanized.admin.domains_label', {}, true),
        help: app.translator.trans('anto0102-sanized.admin.domains_help', {}, true),
        type: 'textarea',
        placeholder: 'example.com\notherdomain.org',
      }),
      90
    )
    .setting(
      () => ({
        setting: 'anto0102-sanized.message',
        label: app.translator.trans('anto0102-sanized.admin.message_label', {}, true),
        help: app.translator.trans('anto0102-sanized.admin.message_help', {}, true),
        type: 'text',
        placeholder: 'Registration with this email domain is not allowed.',
      }),
      80
    )
    .setting(
      () => ({
        setting: 'anto0102-sanized.check_mx',
        label: app.translator.trans('anto0102-sanized.admin.check_mx_label', {}, true),
        help: app.translator.trans('anto0102-sanized.admin.check_mx_help', {}, true),
        type: 'boolean',
      }),
      70
    ),
];
