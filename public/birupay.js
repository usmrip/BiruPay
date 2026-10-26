/**
 * BiruPay Drop-in Client SDK
 * Multi-Platform Centralized Payment Gateway
 * Biru Indonesia Creative (biru.co.id)
 * Version: 1.0.0
 */
(function(window, document) {
    'use strict';

    if (window.BiruPay) {
        return;
    }

    var BIRUPAY_DEFAULT_HOST = 'https://biru.co.id/pembayaran';

    // Script loader cache
    var loadedScripts = {};

    function loadScript(url, callback, attributes) {
        if (!url) {
            if (callback) callback();
            return;
        }
        if (loadedScripts[url] || document.querySelector('script[src="' + url + '"]')) {
            loadedScripts[url] = true;
            if (callback) callback();
            return;
        }
        var script = document.createElement('script');
        script.type = 'text/javascript';
        script.src = url;
        if (attributes) {
            for (var k in attributes) {
                if (attributes.hasOwnProperty(k)) {
                    script.setAttribute(k, attributes[k]);
                }
            }
        }
        script.onload = function() {
            loadedScripts[url] = true;
            if (callback) callback();
        };
        script.onerror = function() {
            console.error('[BiruPay] Failed to load external script:', url);
        };
        document.head.appendChild(script);
    }

    var BiruPay = {
        version: '1.0.1',
        baseUrl: BIRUPAY_DEFAULT_HOST,

        setBaseUrl: function(url) {
            this.baseUrl = url.replace(/\/+$/, '');
        },

        /**
         * Open payment popup given token or gateway reference directly
         */
        open: function(options) {
            options = options || {};
            var gateway = options.gateway || 'duitku';
            var token = options.token || options.snapToken || options.snap_token || options.gatewayReference || options.gateway_reference;
            var gatewayPaymentUrl = options.gatewayPaymentUrl || options.gateway_payment_url;
            var paymentUrl = options.paymentUrl || options.payment_url || gatewayPaymentUrl;
            var scriptUrl = options.scriptUrl || options.script_url;
            // Auto-detect sandbox mode if token starts with 'DS' (Duitku Sandbox)
            var mode = options.mode || ((token && typeof token === 'string' && token.indexOf('DS') === 0) ? 'sandbox' : 'production');

            var onSuccess = function(result) {
                if (typeof options.onSuccess === 'function') {
                    options.onSuccess(result);
                }
                if (options.successUrl) {
                    window.location.href = options.successUrl;
                }
            };
            var onPending = function(result) {
                if (typeof options.onPending === 'function') {
                    options.onPending(result);
                }
                if (options.pendingUrl) {
                    window.location.href = options.pendingUrl;
                }
            };
            var onError = function(result) {
                if (typeof options.onError === 'function') {
                    options.onError(result);
                } else if (options.errorUrl) {
                    window.location.href = options.errorUrl;
                } else {
                    alert('Pembayaran dibatalkan atau gagal.');
                }
            };
            var onClose = options.onClose || function() {};

            // 1. DUITKU GATEWAY
            if (gateway === 'duitku') {
                var duitkuJs = scriptUrl || (mode === 'sandbox' ? 'https://app-sandbox.duitku.com/lib/js/duitku.js' : 'https://app-prod.duitku.com/lib/js/duitku.js');

                loadScript(duitkuJs, function() {
                    if (window.checkout && token) {
                        try {
                            window.checkout.process(token, {
                                defaultLanguage: 'id',
                                successEvent: function(result) { onSuccess(result); },
                                pendingEvent: function(result) { onPending(result); },
                                errorEvent:   function(result) { 
                                    if (gatewayPaymentUrl || paymentUrl) {
                                        window.location.href = (gatewayPaymentUrl || paymentUrl);
                                    } else {
                                        onError(result);
                                    }
                                },
                                closeEvent:   function() { onClose(); }
                            });
                        } catch (e) {
                            console.warn('[BiruPay] Duitku popup fallback to URL:', e);
                            if (gatewayPaymentUrl || paymentUrl) {
                                window.location.href = (gatewayPaymentUrl || paymentUrl);
                            } else {
                                onError({ message: e.message || 'Gagal memuat popup Duitku' });
                            }
                        }
                    } else if (gatewayPaymentUrl || paymentUrl) {
                        window.location.href = (gatewayPaymentUrl || paymentUrl);
                    }
                });
            }
            // 2. MIDTRANS GATEWAY
            else if (gateway === 'midtrans') {
                var midtransJs = scriptUrl || (mode === 'sandbox' ? 'https://app.sandbox.midtrans.com/snap/snap.js' : 'https://app.midtrans.com/snap/snap.js');
                var clientKey = options.client_key || options.clientKey || '';
                var attrs = clientKey ? { 'data-client-key': clientKey } : null;
                loadScript(midtransJs, function() {
                    if (window.snap && token) {
                        window.snap.pay(token, {
                            onSuccess: function(result) { onSuccess(result); },
                            onPending: function(result) { onPending(result); },
                            onError:   function(result) { onError(result); },
                            onClose:   function() { onClose(); }
                        });
                    } else if (gatewayPaymentUrl || paymentUrl) {
                        window.location.href = (gatewayPaymentUrl || paymentUrl);
                    }
                }, attrs);
            }
            // 3. OTHER / DOKU / REDIRECT GATEWAYS
            else if (gatewayPaymentUrl || paymentUrl) {
                window.location.href = (gatewayPaymentUrl || paymentUrl);
            }
        },

        openPopup: function(options) {
            return this.open(options);
        },

        pay: function(params) {
            return this.checkout(params);
        },

        /**
         * Main function: Request payment creation to Biru PayHub, then immediately launch the popup
         */
        checkout: function(params) {
            var self = this;
            var apiKey   = params.apiKey || params.api_key;
            var endpoint = params.endpoint || (this.baseUrl + '/api/v1/payment/create');

            // Callbacks
            var onSuccess = params.onSuccess || function(res) {
                console.log('[BiruPay] Payment success:', res);
            };
            var onPending = params.onPending || function(res) {
                console.log('[BiruPay] Payment pending:', res);
            };
            var onError = params.onError || function(err) {
                alert('[BiruPay] ' + (err.message || err));
            };
            var onClose = params.onClose || function() {};

            if (!apiKey) {
                onError({ message: 'Parameter apiKey wajib disertakan pada BiruPay.checkout()' });
                return;
            }

            if (!params.amount || params.amount <= 0) {
                onError({ message: 'Nominal pembayaran (amount) harus lebih dari 0' });
                return;
            }

            var payload = {
                order_id:       params.orderId || params.order_id || ('ORDER-' + Date.now()),
                amount:         parseFloat(params.amount),
                customer_name:  params.customerName || params.customer_name || 'Pelanggan',
                customer_email: params.customerEmail || params.customer_email || '',
                customer_phone: params.customerPhone || params.customer_phone || '',
                item_name:      params.itemName || params.item_name || 'Pembayaran Layanan',
                item_details:   params.itemDetails || params.item_details || []
            };

            // Optional gateway override if client explicitly wants a specific gateway
            if (params.gateway) {
                payload.gateway = params.gateway;
            }

            // Optional mode override: 'live' / 'production' or 'sandbox' (default is sandbox)
            if (params.mode) {
                payload.mode = params.mode;
            }

            // Create loading UI or call beforeSend
            if (typeof params.onLoading === 'function') {
                params.onLoading(true);
            }

            // Send AJAX / Fetch request to Biru PayHub
            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Biru-ApiKey': apiKey
                },
                body: JSON.stringify(payload)
            })
            .then(function(response) {
                return response.json().then(function(json) {
                    return { ok: response.ok, status: response.status, data: json };
                });
            })
            .then(function(res) {
                if (typeof params.onLoading === 'function') {
                    params.onLoading(false);
                }

                if (!res.ok || res.data.status !== 'success') {
                    var errorMsg = (res.data && res.data.message) ? res.data.message : ('Gagal membuat pembayaran (HTTP ' + res.status + ')');
                    onError({ message: errorMsg, raw: res.data });
                    return;
                }

                var paymentData = res.data.data;

                // Launch popup
                self.open({
                    gateway:             paymentData.gateway,
                    token:               paymentData.snap_token || paymentData.gateway_reference,
                    payment_url:         paymentData.payment_url,
                    gateway_payment_url: paymentData.gateway_payment_url,
                    mode:                paymentData.mode || 'production',
                    script_url:          paymentData.script_url,
                    onSuccess: function(result) {
                        onSuccess({ order_id: paymentData.order_id, trx_reference: paymentData.trx_reference, gateway_result: result });
                    },
                    onPending: function(result) {
                        onPending({ order_id: paymentData.order_id, trx_reference: paymentData.trx_reference, gateway_result: result });
                    },
                    onError: function(err) {
                        onError(err);
                    },
                    onClose: function() {
                        onClose();
                    }
                });
            })
            .catch(function(err) {
                if (typeof params.onLoading === 'function') {
                    params.onLoading(false);
                }
                console.error('[BiruPay] Network/fetch error:', err);
                onError({ message: 'Terjadi kesalahan jaringan saat menghubungi Biru PayHub.', originalError: err });
            });
        },

        /**
         * Easy Form Binder: Attach payment trigger to any HTML <form> (e.g. formulir.vip)
         */
        bindForm: function(formSelector, config) {
            config = config || {};
            var form = typeof formSelector === 'string' ? document.querySelector(formSelector) : formSelector;
            if (!form) {
                console.warn('[BiruPay] Form not found:', formSelector);
                return;
            }

            var self = this;
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                var getVal = function(names) {
                    for (var i = 0; i < names.length; i++) {
                        var el = form.querySelector('[name="' + names[i] + '"]') || form.querySelector('#' + names[i]);
                        if (el && el.value) return el.value.trim();
                    }
                    return '';
                };

                var amount = config.amount || parseFloat(getVal(['amount', 'nominal', 'total', 'biaya', 'harga'])) || 0;
                var customerName = config.customerName || getVal(['name', 'nama', 'customer_name', 'nama_lengkap', 'fullname']) || 'Pelanggan';
                var customerEmail = config.customerEmail || getVal(['email', 'customer_email', 'surel']) || '';
                var customerPhone = config.customerPhone || getVal(['phone', 'telepon', 'hp', 'wa', 'whatsapp', 'no_hp']) || '';
                var itemName = config.itemName || getVal(['item_name', 'produk', 'layanan', 'paket', 'judul']) || document.title || 'Formulir Pembayaran';
                var orderId = config.orderId || getVal(['order_id', 'invoice', 'nomor_pesanan']) || ('VIP-' + Date.now());
                var mode = config.mode || form.getAttribute('data-mode') || getVal(['mode']) || '';

                var submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
                var originalText = submitBtn ? submitBtn.innerHTML : '';

                self.checkout({
                    apiKey:        config.apiKey,
                    endpoint:      config.endpoint,
                    orderId:       orderId,
                    amount:        amount,
                    mode:          mode,
                    customerName:  customerName,
                    customerEmail: customerEmail,
                    customerPhone: customerPhone,
                    itemName:      itemName,
                    onLoading: function(isLoading) {
                        if (submitBtn) {
                            submitBtn.disabled = isLoading;
                            if (isLoading) {
                                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyiapkan Pembayaran...';
                            } else {
                                submitBtn.innerHTML = originalText;
                            }
                        }
                    },
                    onSuccess: function(res) {
                        if (config.onSuccess) {
                            config.onSuccess(res);
                        } else if (config.successUrl) {
                            window.location.href = config.successUrl + (config.successUrl.indexOf('?') >= 0 ? '&' : '?') + 'order_id=' + res.order_id;
                        } else {
                            form.submit();
                        }
                    },
                    onPending: function(res) {
                        if (config.onPending) config.onPending(res);
                    },
                    onError: function(err) {
                        if (config.onError) config.onError(err);
                        else alert('Gagal memproses pembayaran: ' + err.message);
                    }
                });
            });
        },

        /**
         * Auto-initialize elements with [data-birupay-btn] or .birupay-button
         */
        initButtons: function() {
            var self = this;
            var buttons = document.querySelectorAll('[data-birupay-button], .birupay-button');
            buttons.forEach(function(btn) {
                if (btn.__birupay_initialized) return;
                btn.__birupay_initialized = true;

                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    var apiKey = btn.getAttribute('data-api-key') || btn.dataset.apiKey;
                    var amount = parseFloat(btn.getAttribute('data-amount') || btn.dataset.amount || 0);
                    var orderId = btn.getAttribute('data-order-id') || btn.dataset.orderId;
                    var itemName = btn.getAttribute('data-item-name') || btn.dataset.itemName;
                    var customerName = btn.getAttribute('data-customer-name') || btn.dataset.customerName;
                    var customerEmail = btn.getAttribute('data-customer-email') || btn.dataset.customerEmail;
                    var customerPhone = btn.getAttribute('data-customer-phone') || btn.dataset.customerPhone;
                    var successUrl = btn.getAttribute('data-success-url') || btn.dataset.successUrl;

                    self.checkout({
                        apiKey: apiKey,
                        amount: amount,
                        orderId: orderId,
                        itemName: itemName,
                        customerName: customerName,
                        customerEmail: customerEmail,
                        customerPhone: customerPhone,
                        onSuccess: function(res) {
                            if (successUrl) {
                                window.location.href = successUrl + (successUrl.indexOf('?') >= 0 ? '&' : '?') + 'order_id=' + res.order_id;
                            } else {
                                alert('Pembayaran berhasil diverifikasi untuk order ' + res.order_id);
                            }
                        }
                    });
                });
            });
        }
    };

    // Auto-init on DOMContentLoaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            BiruPay.initButtons();
        });
    } else {
        BiruPay.initButtons();
    }

    window.BiruPay = BiruPay;

})(window, document);
