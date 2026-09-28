<style>
    body {
        margin: 0;
        padding: 0;
    }

    @media only screen and (max-width: 600px), only screen and (max-device-width: 600px) {
        .email-container {
            width: 100% !important;
            max-width: 100% !important;
            border-radius: 0 !important;
        }

        .email-header,
        .email-body,
        .email-footer {
            padding-left: 20px !important;
            padding-right: 20px !important;
        }

        .email-header {
            padding-top: 22px !important;
            padding-bottom: 22px !important;
        }

        .email-body {
            padding-top: 24px !important;
            padding-bottom: 24px !important;
        }

        .detail-table td {
            padding-left: 14px !important;
            padding-right: 14px !important;
        }

        /* Stack label above value instead of squeezing them side by side */
        .detail-table td:first-child:not([colspan]) {
            display: block !important;
            width: 100% !important;
            box-sizing: border-box !important;
            border-right: none !important;
            border-bottom: none !important;
            padding-bottom: 2px !important;
            font-size: 11px !important;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .detail-table td:last-child:not([colspan]) {
            display: block !important;
            width: 100% !important;
            box-sizing: border-box !important;
            padding-top: 0 !important;
            padding-bottom: 14px !important;
        }

        .cta-table {
            width: 100% !important;
        }

        .cta-table td {
            display: block !important;
            width: 100% !important;
            text-align: center !important;
        }

        .cta-button {
            display: block !important;
            width: 100% !important;
            box-sizing: border-box !important;
            text-align: center !important;
        }
    }
</style>
