# MCP Server

Lara Dashboard ships an [MCP](https://modelcontextprotocol.io) server at `/mcp`. AI clients such as Claude, Cursor and Claude Code can use it to manage posts, media, terms, email templates and module tools, all within the connected user's permissions.

The server is **off by default**. Turn it on under **Settings → MCP**. While it's off, `/mcp` and every OAuth endpoint below return 404.

## Connecting a client

There are two ways to connect. Both end up with the same kind of credential: an MCP agent token whose abilities follow the user's permissions.

### 1. Agent token (Cursor, Claude Desktop, Claude Code, scripts)

Generate a token under **Settings → MCP → Agent tokens**, then send it as a bearer header:

```json
{
    "mcpServers": {
        "laradashboard": {
            "url": "https://your-site.com/mcp",
            "headers": { "Authorization": "Bearer YOUR_MCP_AGENT_TOKEN" }
        }
    }
}
```

### 2. OAuth (Claude.ai custom connectors and other remote clients)

Clients that can't send custom headers connect with just the URL:

1. Add `https://your-site.com/mcp` as a custom connector in the client.
2. The client gets a `401` whose `WWW-Authenticate` header points at `/.well-known/oauth-protected-resource/mcp`. From there it discovers the authorization server and registers itself (RFC 7591).
3. Your browser opens the Lara Dashboard consent screen. Sign in, review the abilities, and click **Allow**.
4. The client receives a short-lived access token (60 minutes) and a rotating refresh token (30 days). It refreshes them automatically.

OAuth connections appear in the agent token list as `OAuth · <client name>`. Revoke one there to disconnect the client. Its next refresh fails and it has to ask for approval again.

| Endpoint | Purpose |
|---|---|
| `GET /.well-known/oauth-protected-resource/{path?}` | Protected resource metadata (RFC 9728) |
| `GET /.well-known/oauth-authorization-server` | Authorization server metadata (RFC 8414) |
| `POST /mcp/oauth/register` | Dynamic client registration (RFC 7591), rate-limited |
| `GET/POST /mcp/oauth/authorize` | Consent screen (signed-in users) |
| `POST /mcp/oauth/token` | `authorization_code` and `refresh_token` grants, rate-limited |

#### Security model

- Only public clients are supported, and PKCE with `S256` is mandatory.
- Redirect URIs must be HTTPS, except `http://localhost` and loopback addresses for desktop clients. They're matched exactly, except that loopback redirects may use any port (RFC 8252).
- Registration ignores unsupported client metadata, such as a requested client secret, and returns what was actually registered (RFC 7591 §3.2.1).
- Once the client and redirect URI are verified, errors such as a missing PKCE challenge or a user without permission go back to the client as `error=invalid_request` or `error=access_denied`, together with the original `state`. Successful responses include `iss` (RFC 9207).
- Authorization codes are single-use and expire after 10 minutes. Codes and refresh tokens are stored as SHA-256 hashes.
- Refresh tokens rotate on every use. Reusing an old one revokes the whole grant for that client and user.
- Approving a client follows the same rules as creating an agent token by hand: the user needs the `manage` settings permission and at least `post.view` or `post.create`, and it's disabled in demo mode.
- Every refresh re-checks those same rules and recalculates abilities from the user's current permissions. If a check fails, the grant is revoked.
- Revoking an `OAuth · …` token in Settings also revokes its refresh token, so the client can't reconnect without asking again.
- Protocol errors (`invalid_grant` and similar) are returned to the client but not logged as application errors.
- The scheduler prunes expired codes and refresh tokens daily, keeping revoked refresh tokens for 7 days so reuse can still be detected. It also prunes expired Sanctum tokens (`model:prune` and `sanctum:prune-expired`).
- The consent screen sends `X-Frame-Options: DENY` and `frame-ancestors 'none'`.

#### Web server note

Make sure your web server doesn't block `/.well-known/` paths. Some nginx configs deny every dot-path. Allow it with:

```nginx
location ^~ /.well-known/ {
    try_files $uri $uri/ /index.php?$query_string;
}
```
