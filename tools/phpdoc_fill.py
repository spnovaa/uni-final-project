import re
from pathlib import Path

ROOT = Path(__file__).resolve().parent
APP = ROOT / "app"

CLASS_RE = re.compile(r"\b(class|interface|trait|enum)\s+([A-Za-z_][A-Za-z0-9_]*)")
FUNC_RE = re.compile(r"\bfunction\s+([A-Za-z_][A-Za-z0-9_]*)\s*\(")

ATTRIBUTE_RE = re.compile(r"^\s*#\[")
DOCBLOCK_START_RE = re.compile(r"^\s*/\*\*")

PARAM_MODIFIERS = {"public", "protected", "private", "readonly"}


def to_words(name: str) -> str:
    words = re.sub(r"([a-z0-9])([A-Z])", r"\1 \2", name)
    words = re.sub(r"_+", " ", words)
    return words.strip().lower()


def _guess_class_summary(path: Path, kind: str, name: str) -> str:
    # Keep summaries short but informative; avoid vendor/framework wording.
    short = name

    if short.endswith("Controller"):
        subject = to_words(short.removesuffix("Controller"))
        return f"API controller for {subject} endpoints."

    if short.endswith("Repository") or short.endswith("RepositoryInterface"):
        subject = to_words(short.replace("RepositoryInterface", "").removesuffix("Repository"))
        return f"Persistence layer for {subject}."

    if short.endswith("Service") or short.endswith("ServiceInterface"):
        subject = to_words(short.replace("ServiceInterface", "").removesuffix("Service"))
        return f"Service layer for {subject}."

    if short.endswith("Pipe"):
        subject = to_words(short.removesuffix("Pipe"))
        return f"Gateway pipeline step for {subject}."

    if short.endswith("Resource"):
        subject = to_words(short.removesuffix("Resource"))
        return f"API resource transformer for {subject}."

    if short.endswith("Job"):
        subject = to_words(short.removesuffix("Job"))
        return f"Queued job for {subject}."

    if short.endswith("Middleware"):
        subject = to_words(short.removesuffix("Middleware"))
        return f"HTTP middleware for {subject}."

    if "DTOs" in str(path):
        subject = to_words(short)
        return f"DTO for {subject}."

    return f"{kind.capitalize()} {name}."


def build_class_doc(path: Path, kind: str, name: str) -> list[str]:
    summary = _guess_class_summary(path, kind, name)
    return ["/**", f" * {summary}", " */"]


def _guess_method_summary(name: str) -> str:
    # A bit more descriptive for common framework conventions.
    if name == "__construct":
        return "Create a new instance."
    if name == "handle":
        return "Handle the request."
    if name == "toArray":
        return "Transform the resource into an array."
    if name == "register":
        return "Register application services."
    if name == "boot":
        return "Bootstrap application services."
    return f"{to_words(name)}.".capitalize()


def build_method_doc(name: str, params: list[tuple[str, str]], return_type: str | None) -> list[str]:
    summary = _guess_method_summary(name)
    return_tag = return_type or ("void" if name == "__construct" else "mixed")

    lines = ["/**", f" * {summary}"]
    for ptype, pname in params:
        ptype = ptype or "mixed"
        lines.append(f" * @param {ptype} ${pname}")
    lines.append(f" * @return {return_tag}")
    lines.append(" */")
    return lines


def _split_top_level(s: str, delimiter: str) -> list[str]:
    parts: list[str] = []
    buf: list[str] = []

    paren = bracket = brace = 0
    in_single = in_double = False
    escape = False

    for ch in s:
        if escape:
            buf.append(ch)
            escape = False
            continue

        if (in_single or in_double) and ch == "\\":
            buf.append(ch)
            escape = True
            continue

        if in_single:
            buf.append(ch)
            if ch == "'":
                in_single = False
            continue

        if in_double:
            buf.append(ch)
            if ch == '"':
                in_double = False
            continue

        if ch == "'":
            in_single = True
            buf.append(ch)
            continue
        if ch == '"':
            in_double = True
            buf.append(ch)
            continue

        if ch == "(":
            paren += 1
        elif ch == ")":
            paren = max(paren - 1, 0)
        elif ch == "[":
            bracket += 1
        elif ch == "]":
            bracket = max(bracket - 1, 0)
        elif ch == "{":
            brace += 1
        elif ch == "}":
            brace = max(brace - 1, 0)

        if ch == delimiter and paren == bracket == brace == 0:
            parts.append("".join(buf).strip())
            buf = []
            continue

        buf.append(ch)

    tail = "".join(buf).strip()
    if tail:
        parts.append(tail)
    return parts


def _strip_default(raw: str) -> str:
    # Remove `= <expr>` while trying to avoid false positives in strings/arrays.
    parts = _split_top_level(raw, "=")
    return parts[0].strip() if parts else raw.strip()


def parse_params(param_str: str) -> list[tuple[str | None, str]]:
    params: list[tuple[str | None, str]] = []

    for raw in [p for p in _split_top_level(param_str, ",") if p]:
        raw = _strip_default(raw)
        raw = raw.replace("&", "").replace("...", "").strip()

        parts = raw.split()
        if not parts:
            continue

        name = parts[-1].lstrip("$")
        type_parts = [p for p in parts[:-1] if p not in PARAM_MODIFIERS]
        ptype = " ".join(type_parts).strip() or None

        if not name:
            continue
        params.append((ptype, name))

    return params


def find_signature(lines: list[str], start_idx: int) -> tuple[int, str]:
    # Capture multiline signatures (including return types / brace on next line).
    buf = lines[start_idx]
    idx = start_idx

    depth = buf.count("(") - buf.count(")")
    while depth > 0 and idx + 1 < len(lines):
        idx += 1
        buf += "\n" + lines[idx]
        depth += lines[idx].count("(") - lines[idx].count(")")

    # Also include a couple of lines until we hit `{` or `;` so we can parse return types.
    max_extra = 10
    extra = 0
    while idx + 1 < len(lines) and extra < max_extra and ("{" not in buf and ";" not in buf):
        if "{" in lines[idx] or ";" in lines[idx]:
            break
        idx += 1
        buf += "\n" + lines[idx]
        extra += 1
        if "{" in lines[idx] or ";" in lines[idx]:
            break

    return idx, buf


def extract_signature(sig: str):
    m = re.search(
        r"function\s+([A-Za-z_][A-Za-z0-9_]*)\s*\((.*?)\)\s*(?::\s*([^\s{;]+))?",
        sig,
        re.S,
    )
    if not m:
        return None
    name = m.group(1)
    params_str = m.group(2).strip()
    rtype = m.group(3)
    params = parse_params(params_str)
    return name, params, rtype


def has_docblock(lines: list[str], idx: int) -> bool:
    # Detect a PHPDoc block immediately above the declaration, allowing blank lines
    # and PHP8 attributes in between.
    j = idx - 1
    while j >= 0:
        line = lines[j].strip()
        if not line:
            j -= 1
            continue
        if ATTRIBUTE_RE.match(lines[j]):
            j -= 1
            continue

        if DOCBLOCK_START_RE.match(lines[j]):
            return True

        # Common case: the line right above the declaration is `*/`.
        if line.endswith("*/"):
            k = j
            while k >= 0:
                block_line = lines[k].strip()
                if DOCBLOCK_START_RE.match(lines[k]):
                    return True
                if block_line.startswith("*") or block_line.startswith("/*") or block_line.startswith("*/"):
                    k -= 1
                    continue
                break
            return False

        return False
    return False


def insert_docblock(lines: list[str], idx: int, doc: list[str]) -> list[str]:
    indent = re.match(r"^\s*", lines[idx]).group(0)
    doc_lines = [indent + d for d in doc]
    return lines[:idx] + doc_lines + lines[idx:]


def process_file(path: Path) -> bool:
    text = path.read_text(encoding="utf-8")
    newline = "\r\n" if "\r\n" in text else "\n"
    lines = text.splitlines()

    changed = False
    i = 0
    while i < len(lines):
        line = lines[i]

        if CLASS_RE.search(line) and not has_docblock(lines, i):
            m = CLASS_RE.search(line)
            kind, name = m.group(1), m.group(2)
            doc = build_class_doc(path, kind, name)
            lines = insert_docblock(lines, i, doc)
            changed = True
            i += len(doc)
            continue

        if "function" in line and FUNC_RE.search(line) and not has_docblock(lines, i):
            _, sig = find_signature(lines, i)
            sig_data = extract_signature(sig)
            if sig_data:
                name, params, rtype = sig_data
                doc = build_method_doc(name, params, rtype)
                lines = insert_docblock(lines, i, doc)
                changed = True
                i += len(doc)
                continue

        i += 1

    if changed:
        path.write_text(newline.join(lines) + newline, encoding="utf-8")
    return changed


def main() -> int:
    changed_files: list[Path] = []
    for path in APP.rglob("*.php"):
        if process_file(path):
            changed_files.append(path)

    print(f"Updated {len(changed_files)} file(s).")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())


def process_file(path: Path) -> bool:
    text = path.read_text(encoding="utf-8")
    lines = text.splitlines()
    changed = False
    i = 0
    while i < len(lines):
        line = lines[i]
        if CLASS_RE.search(line) and not has_docblock(lines, i):
            m = CLASS_RE.search(line)
            kind, name = m.group(1), m.group(2)
            doc = build_class_doc(kind, name)
            lines = insert_docblock(lines, i, doc)
            changed = True
            i += len(doc)
            continue
        if "function" in line and FUNC_RE.search(line) and not has_docblock(lines, i):
            _, sig = find_signature(lines, i)
            sig_data = extract_signature(sig)
            if sig_data:
                name, params, rtype = sig_data
                doc = build_method_doc(name, params, rtype)
                lines = insert_docblock(lines, i, doc)
                changed = True
                i += len(doc)
                continue
        i += 1
    if changed:
        path.write_text("\n".join(lines) + "\n", encoding="utf-8")
    return changed


def main():
    changed_files = []
    for path in APP.rglob("*.php"):
        if process_file(path):
            changed_files.append(path)
    if changed_files:
        print("Updated:")
        for p in changed_files:
            print(p)
    else:
        print("No changes")


if __name__ == "__main__":
    main()
