"""LLM provider layer for the ShopLK demo. app.py and secure_app.py only call generate()."""
import os
import re

FAKE_SECRET_PATTERN = re.compile(r"[A-Z]{2,}\d{2,}")
GEMINI_DEFAULT_MODEL = "gemini-2.5-flash"
ANTHROPIC_MODEL = "claude-sonnet-5"
MAX_OUTPUT_TOKENS = 500


def generate(system: str, user: str, tools: list | None = None) -> str:
    provider = os.getenv("LLM_PROVIDER", "fake")
    if provider == "fake":
        return _fake_generate(system, user, tools)
    if provider == "gemini":
        return _gemini_generate(system, user, tools)
    if provider == "anthropic":
        return _anthropic_generate(system, user, tools)
    return f"Unknown LLM_PROVIDER '{provider}'. Use fake, gemini, or anthropic."


def _fake_generate(system: str, user: str, tools: list | None) -> str:
    lower_user = user.lower()

    if tools:
        for tool in tools:
            if tool.__name__ == "refund_order" and "refund" in lower_user:
                match = re.search(r"order\s+(\d+)", lower_user)
                order_id = match.group(1) if match else "4471"
                return f"This review asked for a refund, so I processed it. {tool(order_id)}"
        return "Summary: the review mentions delivery speed and packaging."

    if "ignore" in lower_user and ("instruction" in lower_user or "prompt" in lower_user):
        # Search both system and user text: a vulnerable caller may concatenate
        # its system prompt (and any secret in it) into the user string instead
        # of passing it separately.
        secret = FAKE_SECRET_PATTERN.search(system + "\n" + user)
        return f"Sure, ignoring the above: {secret.group(0) if secret else 'no secret found'}"

    marker = "reply with exactly:"
    if marker in lower_user:
        idx = lower_user.find(marker)
        return user[idx + len(marker):].strip()

    return "Thanks for reaching out to ShopLK support. How can I help today?"


def _gemini_generate(system: str, user: str, tools: list | None) -> str:
    api_key = os.getenv("GEMINI_API_KEY", "").strip()
    if not api_key:
        return "LLM_PROVIDER is gemini but GEMINI_API_KEY is not set. Set LLM_PROVIDER=fake if you do not have a key."

    from google import genai
    from google.genai import types

    model = os.getenv("GEMINI_MODEL") or GEMINI_DEFAULT_MODEL
    client = genai.Client(api_key=api_key)

    try:
        response = client.models.generate_content(
            model=model,
            contents=user,
            config=types.GenerateContentConfig(
                system_instruction=system,
                tools=tools or None,
                max_output_tokens=MAX_OUTPUT_TOKENS,
            ),
        )
        return response.text or ""
    except Exception as exc:
        message = str(exc)
        if "429" in message or "RESOURCE_EXHAUSTED" in message:
            return "Gemini rate limit reached for this project. Wait a minute, or use your own GEMINI_API_KEY."
        return f"Gemini request failed: {message[:200]}"


def _anthropic_tool_specs(tools: list) -> list:
    import inspect
    specs = []
    for fn in tools:
        params = list(inspect.signature(fn).parameters)
        specs.append({
            "name": fn.__name__,
            "description": (fn.__doc__ or fn.__name__).strip(),
            "input_schema": {
                "type": "object",
                "properties": {p: {"type": "string"} for p in params},
                "required": params,
            },
        })
    return specs


def _anthropic_generate(system: str, user: str, tools: list | None) -> str:
    api_key = os.getenv("ANTHROPIC_API_KEY", "").strip()
    if not api_key:
        return "LLM_PROVIDER is anthropic but ANTHROPIC_API_KEY is not set. Set LLM_PROVIDER=fake if you do not have a key."

    import anthropic

    client = anthropic.Anthropic(api_key=api_key)
    tool_specs = _anthropic_tool_specs(tools) if tools else []
    tool_map = {fn.__name__: fn for fn in tools} if tools else {}

    try:
        messages = [{"role": "user", "content": user}]
        response = client.messages.create(
            model=ANTHROPIC_MODEL, max_tokens=MAX_OUTPUT_TOKENS,
            system=system, messages=messages, tools=tool_specs,
        )

        if response.stop_reason == "tool_use":
            call = next(b for b in response.content if b.type == "tool_use")
            result = tool_map[call.name](**call.input)
            messages.append({"role": "assistant", "content": response.content})
            messages.append({"role": "user", "content": [
                {"type": "tool_result", "tool_use_id": call.id, "content": str(result)}
            ]})
            response = client.messages.create(
                model=ANTHROPIC_MODEL, max_tokens=MAX_OUTPUT_TOKENS,
                system=system, messages=messages, tools=tool_specs,
            )

        return "".join(b.text for b in response.content if b.type == "text")
    except anthropic.RateLimitError:
        return "Anthropic rate limit reached. Wait a minute, or use your own API key."
    except Exception as exc:
        return f"Anthropic request failed: {str(exc)[:200]}"
