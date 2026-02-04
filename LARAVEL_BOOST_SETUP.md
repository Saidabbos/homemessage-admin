# Laravel Boost - AI-Assisted Development Setup

## 🤖 What is Laravel Boost?

Laravel Boost is a development package that accelerates **AI-assisted development** by providing essential guidelines and skills to help AI agents write high-quality Laravel applications that follow Laravel best practices.

### Components:

1. **AI Guidelines** - Context files that teach AI about your project
2. **Agent Skills** - Reusable knowledge modules for specific tasks
3. **MCP Server** - Model Context Protocol for tool integration
4. **Documentation API** - Access to 17,000+ Laravel resources

---

## 📦 Installation

Installed packages:
- `laravel/boost` (v2.0.6) - Main package
- `laravel/mcp` (v0.5.4) - Model Context Protocol server
- `laravel/roster` (v0.2.9) - Agent configuration manager

Install command run:
```bash
php artisan boost:install
```

---

## 📁 Project Structure

```
.ai/
├── guidelines/              # Context for AI agents
│   └── project/
│       ├── architecture.blade.php      # Overall project structure
│       ├── permissions.blade.php       # Role/permission system
│       └── translatable.blade.php      # Multi-language system
│
└── skills/                  # Task-specific knowledge
    ├── spatie-integration/
    │   └── SKILL.md                    # Working with Spatie packages
    ├── translatable-models/
    │   └── SKILL.md                    # Creating multi-language models
    └── permission-system/
        └── SKILL.md                    # Implementing authorization
```

---

## 📚 Guidelines Overview

### 1. Architecture Guidelines (.ai/guidelines/project/architecture.blade.php)

Provides context about:
- Project models (User, Post, Category)
- Database structure
- Directory structure
- Key patterns and conventions
- Spatie package integration
- Test user credentials

**When AI uses this**: When understanding overall project structure, creating new models, or implementing features

### 2. Permissions Guidelines (.ai/guidelines/project/permissions.blade.php)

Explains:
- Current roles (admin, editor, writer)
- Available permissions
- Permission checking patterns
- Middleware protection
- Authorization policies
- Blade template directives
- Database tables structure

**When AI uses this**: When implementing access control, creating protected routes, checking permissions

### 3. Translatable Guidelines (.ai/guidelines/project/translatable.blade.php)

Covers:
- Supported languages (en, uz, ru)
- Translatable model setup
- Database structure
- Creating/updating/retrieving translations
- Locale management
- Controller implementation
- Common issues and solutions

**When AI uses this**: When creating multi-language features, managing translations, building locale-aware controllers

---

## 🎯 Skills Overview

### Skill 1: spatie-integration

**File**: `.ai/skills/spatie-integration/SKILL.md`

**Purpose**: Comprehensive guide for working with both Spatie packages

**Covers**:
- Creating translatable models
- Permission system implementation
- Role-based access control
- Authorization patterns
- Migration structure
- Testing checklist
- Common patterns

**Use when**: Working with either Spatie package, need consistent patterns

### Skill 2: translatable-models

**File**: `.ai/skills/translatable-models/SKILL.md`

**Purpose**: Deep dive into creating and managing translatable models

**Covers**:
- Step-by-step model creation
- Migration structure
- Factory creation
- Seeder examples
- Usage patterns
- Controller implementation
- Best practices
- Troubleshooting

**Use when**: Creating new translatable models, building multi-language features

### Skill 3: permission-system

**File**: `.ai/skills/permission-system/SKILL.md`

**Purpose**: Complete guide for role and permission management

**Covers**:
- Adding new roles
- Adding new permissions
- Assigning roles to users
- Authorization in controllers
- Route protection
- Blade authorization
- Permission checks
- Testing

**Use when**: Implementing authorization, creating protected routes, managing user access

---

## 🔧 How AI Uses These Files

### When Writing Code

AI agents (like Claude Code/Claude) will:

1. **Read Guidelines** - Understand project architecture, conventions, patterns
2. **Reference Skills** - Apply task-specific knowledge from skill files
3. **Follow Patterns** - Implement code consistent with existing patterns
4. **Check Database** - Use MCP server to inspect schema, query data
5. **Generate Code** - Write code that follows project standards

### Example Workflow

**Task**: "Create a Category model with translations"

1. **AI reads**: `architecture.blade.php` → Understands project structure
2. **AI reads**: `translatable-models/SKILL.md` → Gets step-by-step instructions
3. **AI queries**: Database schema via MCP → Checks existing patterns
4. **AI generates**:
   - Model with HasTranslations trait
   - Migration with translation table
   - Seeder with example data
   - All following project conventions

---

## 💡 Best Practices

### For Maintaining Guidelines

1. **Keep Updated** - Update when adding new features/packages
2. **Be Specific** - Include actual code examples
3. **Document Patterns** - Explain how things are done in this project
4. **Include Examples** - Provide working code snippets
5. **Link to Docs** - Reference official documentation when useful

### For Creating Skills

1. **Task-Focused** - Each skill should be for a specific task
2. **Self-Contained** - Should work independently
3. **Practical** - Include real examples and patterns
4. **Comprehensive** - Cover common use cases and edge cases
5. **Troubleshooting** - Include common issues and solutions

### For Using Boost

1. **Be Specific** - Tell AI exactly what you want
2. **Reference Guidelines** - Mention patterns from guidelines
3. **Ask for Examples** - Request concrete code examples
4. **Test Results** - Verify generated code works
5. **Provide Feedback** - Update guidelines if needed

---

## 📝 Adding New Guidelines

To add context about new features:

```bash
# Create new guideline file
.ai/guidelines/project/your-feature.blade.php

# Include:
# - Feature overview
# - How it works in this project
# - Code examples
# - Database structure
# - Integration points
# - Best practices
```

---

## 🎓 Adding New Skills

To create skill for new task type:

```bash
# Create skill directory
.ai/skills/your-skill-name/SKILL.md

# Include YAML front matter:
---
name: your-skill-name
description: Clear description of what the skill covers
---

# Then add:
# - When to use this skill
# - Key features and patterns
# - Step-by-step instructions
# - Code examples
# - Best practices
# - Troubleshooting
```

---

## 🔌 MCP Server Integration

The installed MCP server provides tools for:

- **Inspect Application**: Query project structure, routes, models
- **Query Database**: Check schema, run queries
- **Execute Code**: Run artisan commands, tinker
- **Access Configuration**: Read config files
- **Analyze Routes**: View route definitions

**How it helps**: AI agents can verify information, check actual database structure, and generate code that matches reality

---

## 🧪 Testing with Boost

When AI generates code:

1. **Verify It Compiles** - Check syntax errors
2. **Run Tests** - Ensure functionality works
3. **Check Database** - Verify migrations work
4. **Test Authorization** - Confirm permissions work
5. **Validate Forms** - Check input validation

```bash
# Common test commands
php artisan tinker           # Test code
php artisan migrate:reset    # Reset database
php artisan db:seed          # Reseed
php artisan test             # Run tests
```

---

## 📚 Integration with Claude Code

Since you're using Claude Code (an AI assistant):

1. **Guidelines Help**: Tell me about your project patterns
2. **Skills Guide**: Help me implement specific features correctly
3. **MCP Server**: Let me check real database state
4. **Context**: Help me write code that fits your project

**Usage**:
```
Human: "Create a translatable Blog model with posts"
Claude (me):
  1. Reads translatable-models/SKILL.md
  2. Reads architecture.blade.php
  3. Checks database patterns via MCP
  4. Generates model, migration, seeder following patterns
```

---

## 🚀 Next Steps

1. **Update Guidelines** - Add more details as project grows
2. **Create More Skills** - Add skills for new features (API, Testing, etc.)
3. **Monitor Quality** - Review AI-generated code regularly
4. **Refine Patterns** - Improve patterns based on experience
5. **Document Changes** - Keep guidelines in sync with code

---

## 📖 Resources

- [Laravel Boost Docs](https://boost.laravel.com)
- [Laravel MCP Docs](https://github.com/laravel/mcp)
- [Spatie Permission Docs](https://spatie.be/docs/laravel-permission)
- [Spatie Translatable Docs](https://spatie.be/docs/laravel-translatable)

---

## ✨ Summary

Laravel Boost provides:

✅ **Guidelines** - Project context and patterns for AI
✅ **Skills** - Task-specific knowledge modules
✅ **Integration** - MCP server for real-time information
✅ **Quality** - Better AI-generated code that fits your project
✅ **Consistency** - All code follows same patterns

**Result**: AI-assisted development that's faster, better, and more consistent! 🚀
