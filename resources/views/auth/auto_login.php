<!DOCTYPE html>
<html>

<head>
    <title>SSO Login</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: sans-serif;
        }

        .loader {
            font-size: 20px;
        }
    </style>
</head>

<body>
    <div class="loader">Logging in...</div>

    <script>
        const APP_ID = "6908a51e8211040f4c681df8";

        // Step 1: Send message to parent requesting SSO token
        window.parent.postMessage({
            type: "REQUEST_SSO_TOKEN",
            appId: APP_ID
        }, "*");

        // Step 2: Listen for parent response
        window.addEventListener("message", async function(event) {
            const data = event.data || {};
            if (data.message !== "SSO_TOKEN_RESPONSE") return;

            const ssoToken = data.ssoToken;
            const appId = data.appId;

            // Step 3: Call Laravel API with SSO token
            try {
                const response = await fetch("/api/validate-sso", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json"
                    },
                    body: JSON.stringify({
                        sso_token: ssoToken,
                        app_id: APP_ID
                    })
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    window.location.href = result.redirect_to; // Step 4: Redirect
                } else {
                    alert("SSO validation failed");
                }
            } catch (e) {
                console.error(e);
                alert("Error validating SSO");
            }
        });
    </script>
</body>

</html>
