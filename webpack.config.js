/**
 * SPDX-FileCopyrightText: 2026 aarekraft.dev - Sash Wegmüller
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

const path = require('path')
const webpackConfig = require('@nextcloud/webpack-vue-config')

webpackConfig.entry.main = path.join(__dirname, 'src', 'main.js')
webpackConfig.entry.admin = path.join(__dirname, 'src', 'admin', 'main.js')

module.exports = webpackConfig
