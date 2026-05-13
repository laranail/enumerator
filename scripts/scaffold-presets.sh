#!/usr/bin/env bash
# scripts/scaffold-presets.sh
#
# Writes the 26 native PHP 8.3+ preset enums in src/Presets/Enums/.
#
# Idempotent.

set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

DIR="src/Presets/Enums"
mkdir -p "$DIR"

# ============================================================================
# Lifecycle / Status
# ============================================================================

cat > "$DIR/StatusEnum.php" <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Simtabi\Laranail\Enumerator\Attributes\{Color, Icon, Label, Order};
use Simtabi\Laranail\Enumerator\Concerns\{HasEnumeratorBehavior, HasGrouping, HasOrder};
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

enum StatusEnum: string implements Enumerator
{
    use HasEnumeratorBehavior;
    use HasGrouping;
    use HasOrder;

    #[Label('Active'),   Color('success'),   Icon('check-circle'), Order(10)] case Active   = 'active';
    #[Label('Inactive'), Color('ghost'),     Icon('pause-circle'), Order(20)] case Inactive = 'inactive';
    #[Label('Pending'),  Color('warning'),   Icon('clock'),        Order(30)] case Pending  = 'pending';
    #[Label('Archived'), Color('secondary'), Icon('archive'),      Order(40)] case Archived = 'archived';

    public static function groups(): array
    {
        return [
            'positive' => [self::Active],
            'negative' => [self::Archived],
            'pending'  => [self::Pending],
        ];
    }
}
PHP

cat > "$DIR/PublicationStatusEnum.php" <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Simtabi\Laranail\Enumerator\Attributes\{Color, Icon, Label, Order};
use Simtabi\Laranail\Enumerator\Concerns\{HasEnumeratorBehavior, HasGrouping, HasLifecycle, HasOrder, HasTransitions};
use Simtabi\Laranail\Enumerator\Contracts\{Enumerator, Stateful};

enum PublicationStatusEnum: string implements Enumerator, Stateful
{
    use HasEnumeratorBehavior;
    use HasGrouping;
    use HasLifecycle;
    use HasOrder;
    use HasTransitions;

    #[Label('Draft'),     Color('secondary'), Icon('pencil'),       Order(10)] case Draft     = 'draft';
    #[Label('Pending'),   Color('warning'),   Icon('clock'),        Order(20)] case Pending   = 'pending';
    #[Label('Published'), Color('success'),   Icon('check-circle'), Order(30)] case Published = 'published';
    #[Label('Archived'),  Color('secondary'), Icon('archive'),      Order(40)] case Archived  = 'archived';
    #[Label('Deleted'),   Color('danger'),    Icon('trash'),        Order(50)] case Deleted   = 'deleted';

    public static function initialStates(): array
    {
        return [self::Draft];
    }

    public static function transitions(): array
    {
        return [
            self::Draft->value     => [self::Pending, self::Archived],
            self::Pending->value   => [self::Published, self::Draft, self::Archived],
            self::Published->value => [self::Archived],
            self::Archived->value  => [self::Deleted, self::Draft],
            self::Deleted->value   => [],
        ];
    }

    public static function groups(): array
    {
        return [
            'positive' => [self::Published],
            'negative' => [self::Deleted],
            'pending'  => [self::Draft, self::Pending],
            'terminal' => [self::Deleted],
        ];
    }
}
PHP

cat > "$DIR/ApprovalStatusEnum.php" <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Simtabi\Laranail\Enumerator\Attributes\{Color, Icon, Label, Order};
use Simtabi\Laranail\Enumerator\Concerns\{HasEnumeratorBehavior, HasGrouping, HasOrder, HasTransitions};
use Simtabi\Laranail\Enumerator\Contracts\{Enumerator, Stateful};

enum ApprovalStatusEnum: string implements Enumerator, Stateful
{
    use HasEnumeratorBehavior;
    use HasGrouping;
    use HasOrder;
    use HasTransitions;

    #[Label('Pending'),        Color('warning'),   Icon('clock'),         Order(10)] case Pending        = 'pending';
    #[Label('Approved'),       Color('success'),   Icon('check-circle'),  Order(20)] case Approved       = 'approved';
    #[Label('Rejected'),       Color('danger'),    Icon('x-circle'),      Order(30)] case Rejected       = 'rejected';
    #[Label('Needs Revision'), Color('info'),      Icon('refresh-ccw'),   Order(40)] case NeedsRevision  = 'needs_revision';
    #[Label('Cancelled'),      Color('secondary'), Icon('slash'),         Order(50)] case Cancelled      = 'cancelled';

    public static function initialStates(): array
    {
        return [self::Pending];
    }

    public static function transitions(): array
    {
        return [
            self::Pending->value        => [self::Approved, self::Rejected, self::NeedsRevision, self::Cancelled],
            self::Approved->value       => [],
            self::Rejected->value       => [self::Pending],
            self::NeedsRevision->value  => [self::Pending, self::Cancelled],
            self::Cancelled->value      => [],
        ];
    }

    public static function groups(): array
    {
        return [
            'positive' => [self::Approved],
            'negative' => [self::Rejected, self::Cancelled],
            'pending'  => [self::Pending, self::NeedsRevision],
            'terminal' => [self::Approved, self::Cancelled],
        ];
    }
}
PHP

cat > "$DIR/OrderStatusEnum.php" <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Simtabi\Laranail\Enumerator\Attributes\{Color, Icon, Label, Order};
use Simtabi\Laranail\Enumerator\Concerns\{HasEnumeratorBehavior, HasGrouping, HasOrder, HasTransitions};
use Simtabi\Laranail\Enumerator\Contracts\{Enumerator, Stateful};

enum OrderStatusEnum: string implements Enumerator, Stateful
{
    use HasEnumeratorBehavior;
    use HasGrouping;
    use HasOrder;
    use HasTransitions;

    #[Label('Pending'),    Color('warning'),   Icon('clock'),        Order(10)] case Pending    = 'pending';
    #[Label('Confirmed'),  Color('info'),      Icon('check'),        Order(20)] case Confirmed  = 'confirmed';
    #[Label('Processing'), Color('info'),      Icon('refresh-ccw'),  Order(30)] case Processing = 'processing';
    #[Label('Shipped'),    Color('primary'),   Icon('truck'),        Order(40)] case Shipped    = 'shipped';
    #[Label('Delivered'),  Color('success'),   Icon('check-circle'), Order(50)] case Delivered  = 'delivered';
    #[Label('Cancelled'),  Color('secondary'), Icon('x-circle'),     Order(60)] case Cancelled  = 'cancelled';
    #[Label('Refunded'),   Color('danger'),    Icon('rotate-ccw'),   Order(70)] case Refunded   = 'refunded';

    public static function initialStates(): array
    {
        return [self::Pending];
    }

    public static function transitions(): array
    {
        return [
            self::Pending->value    => [self::Confirmed, self::Cancelled],
            self::Confirmed->value  => [self::Processing, self::Cancelled],
            self::Processing->value => [self::Shipped, self::Cancelled],
            self::Shipped->value    => [self::Delivered, self::Refunded],
            self::Delivered->value  => [self::Refunded],
            self::Cancelled->value  => [],
            self::Refunded->value   => [],
        ];
    }

    public static function groups(): array
    {
        return [
            'positive' => [self::Delivered],
            'negative' => [self::Cancelled, self::Refunded],
            'pending'  => [self::Pending, self::Confirmed, self::Processing, self::Shipped],
            'terminal' => [self::Delivered, self::Cancelled, self::Refunded],
        ];
    }
}
PHP

cat > "$DIR/PaymentStatusEnum.php" <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Simtabi\Laranail\Enumerator\Attributes\{Color, Icon, Label, Order};
use Simtabi\Laranail\Enumerator\Concerns\{HasEnumeratorBehavior, HasGrouping, HasOrder, HasTransitions};
use Simtabi\Laranail\Enumerator\Contracts\{Enumerator, Stateful};

enum PaymentStatusEnum: string implements Enumerator, Stateful
{
    use HasEnumeratorBehavior;
    use HasGrouping;
    use HasOrder;
    use HasTransitions;

    #[Label('Pending'),    Color('warning'),   Icon('clock'),       Order(10)] case Pending    = 'pending';
    #[Label('Authorized'), Color('info'),      Icon('shield'),      Order(20)] case Authorized = 'authorized';
    #[Label('Captured'),   Color('success'),   Icon('check'),       Order(30)] case Captured   = 'captured';
    #[Label('Failed'),     Color('danger'),    Icon('alert-octagon'), Order(40)] case Failed     = 'failed';
    #[Label('Refunded'),   Color('secondary'), Icon('rotate-ccw'),  Order(50)] case Refunded   = 'refunded';
    #[Label('Voided'),     Color('secondary'), Icon('slash'),       Order(60)] case Voided     = 'voided';
    #[Label('Disputed'),   Color('danger'),    Icon('alert-circle'), Order(70)] case Disputed   = 'disputed';

    public static function initialStates(): array
    {
        return [self::Pending];
    }

    public static function transitions(): array
    {
        return [
            self::Pending->value    => [self::Authorized, self::Failed, self::Voided],
            self::Authorized->value => [self::Captured, self::Voided, self::Failed],
            self::Captured->value   => [self::Refunded, self::Disputed],
            self::Failed->value     => [],
            self::Refunded->value   => [],
            self::Voided->value     => [],
            self::Disputed->value   => [self::Refunded, self::Captured],
        ];
    }

    public static function groups(): array
    {
        return [
            'positive' => [self::Captured],
            'negative' => [self::Failed, self::Voided, self::Refunded, self::Disputed],
            'pending'  => [self::Pending, self::Authorized],
            'terminal' => [self::Failed, self::Voided, self::Refunded],
        ];
    }
}
PHP

cat > "$DIR/CommentStatusEnum.php" <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Simtabi\Laranail\Enumerator\Attributes\{Color, Icon, Label, Order};
use Simtabi\Laranail\Enumerator\Concerns\{HasEnumeratorBehavior, HasGrouping, HasOrder};
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

enum CommentStatusEnum: string implements Enumerator
{
    use HasEnumeratorBehavior;
    use HasGrouping;
    use HasOrder;

    #[Label('Pending'),  Color('warning'),   Icon('clock'),        Order(10)] case Pending  = 'pending';
    #[Label('Approved'), Color('success'),   Icon('check-circle'), Order(20)] case Approved = 'approved';
    #[Label('Spam'),     Color('danger'),    Icon('alert-octagon'), Order(30)] case Spam     = 'spam';
    #[Label('Trash'),    Color('secondary'), Icon('trash'),        Order(40)] case Trash    = 'trash';

    public static function groups(): array
    {
        return [
            'positive' => [self::Approved],
            'negative' => [self::Spam, self::Trash],
            'pending'  => [self::Pending],
        ];
    }
}
PHP

cat > "$DIR/TaskStatusEnum.php" <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Simtabi\Laranail\Enumerator\Attributes\{Color, Icon, Label, Order};
use Simtabi\Laranail\Enumerator\Concerns\{HasEnumeratorBehavior, HasGrouping, HasOrder, HasTransitions};
use Simtabi\Laranail\Enumerator\Contracts\{Enumerator, Stateful};

enum TaskStatusEnum: string implements Enumerator, Stateful
{
    use HasEnumeratorBehavior;
    use HasGrouping;
    use HasOrder;
    use HasTransitions;

    #[Label('To Do'),       Color('secondary'), Icon('list'),         Order(10)] case ToDo       = 'to_do';
    #[Label('In Progress'), Color('info'),      Icon('refresh-ccw'),  Order(20)] case InProgress = 'in_progress';
    #[Label('In Review'),   Color('warning'),   Icon('eye'),          Order(30)] case InReview   = 'in_review';
    #[Label('Done'),        Color('success'),   Icon('check-circle'), Order(40)] case Done       = 'done';
    #[Label('Cancelled'),   Color('secondary'), Icon('x-circle'),     Order(50)] case Cancelled  = 'cancelled';
    #[Label('Blocked'),     Color('danger'),    Icon('alert-circle'), Order(60)] case Blocked    = 'blocked';

    public static function initialStates(): array
    {
        return [self::ToDo];
    }

    public static function transitions(): array
    {
        return [
            self::ToDo->value       => [self::InProgress, self::Cancelled, self::Blocked],
            self::InProgress->value => [self::InReview, self::Done, self::Blocked, self::Cancelled],
            self::InReview->value   => [self::Done, self::InProgress, self::Blocked],
            self::Done->value       => [],
            self::Cancelled->value  => [],
            self::Blocked->value    => [self::ToDo, self::InProgress, self::Cancelled],
        ];
    }

    public static function groups(): array
    {
        return [
            'positive' => [self::Done],
            'negative' => [self::Cancelled, self::Blocked],
            'pending'  => [self::ToDo, self::InProgress, self::InReview],
            'terminal' => [self::Done, self::Cancelled],
        ];
    }
}
PHP

# ============================================================================
# Severity / Weight
# ============================================================================

cat > "$DIR/PriorityEnum.php" <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Simtabi\Laranail\Enumerator\Attributes\{Color, Icon, Label, Order};
use Simtabi\Laranail\Enumerator\Concerns\{HasEnumeratorBehavior, HasGrouping, HasLifecycle, HasOrder};
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

enum PriorityEnum: string implements Enumerator
{
    use HasEnumeratorBehavior;
    use HasGrouping;
    use HasLifecycle;
    use HasOrder;

    #[Label('Low'),      Color('secondary'), Icon('arrow-down'),       Order(10)] case Low      = 'low';
    #[Label('Medium'),   Color('info'),      Icon('minus'),            Order(20)] case Medium   = 'medium';
    #[Label('High'),     Color('warning'),   Icon('arrow-up'),         Order(30)] case High     = 'high';
    #[Label('Urgent'),   Color('danger'),    Icon('zap'),              Order(40)] case Urgent   = 'urgent';
    #[Label('Critical'), Color('danger'),    Icon('alert-octagon'),    Order(50)] case Critical = 'critical';

    public static function groups(): array
    {
        return [
            'high' => [self::High, self::Urgent, self::Critical],
            'low'  => [self::Low, self::Medium],
        ];
    }
}
PHP

cat > "$DIR/SeverityEnum.php" <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Simtabi\Laranail\Enumerator\Attributes\{Color, Icon, Label, Order};
use Simtabi\Laranail\Enumerator\Concerns\{HasEnumeratorBehavior, HasOrder};
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

enum SeverityEnum: int implements Enumerator
{
    use HasEnumeratorBehavior;
    use HasOrder;

    #[Label('Emergency'), Color('danger'),    Icon('siren'),         Order(0)] case Emergency = 0;
    #[Label('Alert'),     Color('danger'),    Icon('alert-triangle'), Order(1)] case Alert    = 1;
    #[Label('Critical'),  Color('danger'),    Icon('alert-octagon'),  Order(2)] case Critical = 2;
    #[Label('Error'),     Color('danger'),    Icon('x-octagon'),      Order(3)] case Error    = 3;
    #[Label('Warning'),   Color('warning'),   Icon('triangle'),       Order(4)] case Warning  = 4;
    #[Label('Notice'),    Color('info'),      Icon('info'),           Order(5)] case Notice   = 5;
    #[Label('Info'),      Color('info'),      Icon('info'),           Order(6)] case Info     = 6;
    #[Label('Debug'),     Color('secondary'), Icon('bug'),            Order(7)] case Debug    = 7;
}
PHP

# ============================================================================
# UI / Presentation
# ============================================================================

cat > "$DIR/VisibilityEnum.php" <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Simtabi\Laranail\Enumerator\Attributes\{Color, Icon, Label, Order};
use Simtabi\Laranail\Enumerator\Concerns\{HasEnumeratorBehavior, HasOrder};
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

enum VisibilityEnum: string implements Enumerator
{
    use HasEnumeratorBehavior;
    use HasOrder;

    #[Label('Public'),     Color('success'),   Icon('globe'), Order(10)] case Public     = 'public';
    #[Label('Private'),    Color('danger'),    Icon('lock'),  Order(20)] case Private    = 'private';
    #[Label('Unlisted'),   Color('secondary'), Icon('eye-off'), Order(30)] case Unlisted = 'unlisted';
    #[Label('Restricted'), Color('warning'),   Icon('shield'), Order(40)] case Restricted = 'restricted';
    #[Label('Internal'),   Color('info'),      Icon('users'), Order(50)] case Internal   = 'internal';
}
PHP

cat > "$DIR/SizeEnum.php" <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Simtabi\Laranail\Enumerator\Attributes\{Label, Order};
use Simtabi\Laranail\Enumerator\Concerns\{HasEnumeratorBehavior, HasLifecycle, HasOrder};
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

enum SizeEnum: string implements Enumerator
{
    use HasEnumeratorBehavior;
    use HasLifecycle;
    use HasOrder;

    #[Label('Extra Small'),         Order(10)] case XSmall  = 'xs';
    #[Label('Small'),               Order(20)] case Small   = 'sm';
    #[Label('Medium'),              Order(30)] case Medium  = 'md';
    #[Label('Large'),               Order(40)] case Large   = 'lg';
    #[Label('Extra Large'),         Order(50)] case XLarge  = 'xl';
    #[Label('Extra Extra Large'),   Order(60)] case XXLarge = 'xxl';
}
PHP

cat > "$DIR/DirectionEnum.php" <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Simtabi\Laranail\Enumerator\Attributes\{Icon, Label};
use Simtabi\Laranail\Enumerator\Concerns\HasEnumeratorBehavior;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

enum DirectionEnum: string implements Enumerator
{
    use HasEnumeratorBehavior;

    #[Label('Ascending'),  Icon('arrow-up')]   case Ascending  = 'asc';
    #[Label('Descending'), Icon('arrow-down')] case Descending = 'desc';
}
PHP

cat > "$DIR/ToggleEnum.php" <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Simtabi\Laranail\Enumerator\Attributes\{Color, Label};
use Simtabi\Laranail\Enumerator\Concerns\HasEnumeratorBehavior;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

enum ToggleEnum: string implements Enumerator
{
    use HasEnumeratorBehavior;

    #[Label('On'),  Color('success')] case On  = 'on';
    #[Label('Off'), Color('secondary')] case Off = 'off';

    public function isOn(): bool { return $this === self::On; }
    public function isOff(): bool { return $this === self::Off; }
    public function toBool(): bool { return $this === self::On; }
}
PHP

# ============================================================================
# HTTP / Web
# ============================================================================

cat > "$DIR/HttpMethodEnum.php" <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumeratorBehavior;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

enum HttpMethodEnum: string implements Enumerator
{
    use HasEnumeratorBehavior;

    #[Label('GET')]     case GET     = 'GET';
    #[Label('POST')]    case POST    = 'POST';
    #[Label('PUT')]     case PUT     = 'PUT';
    #[Label('PATCH')]   case PATCH   = 'PATCH';
    #[Label('DELETE')]  case DELETE  = 'DELETE';
    #[Label('HEAD')]    case HEAD    = 'HEAD';
    #[Label('OPTIONS')] case OPTIONS = 'OPTIONS';

    public function isSafe(): bool   { return in_array($this, [self::GET, self::HEAD, self::OPTIONS], true); }
    public function isIdempotent(): bool { return $this !== self::POST && $this !== self::PATCH; }
}
PHP

cat > "$DIR/HttpStatusClassEnum.php" <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Simtabi\Laranail\Enumerator\Attributes\{Color, Label, Order};
use Simtabi\Laranail\Enumerator\Concerns\{HasEnumeratorBehavior, HasOrder};
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

enum HttpStatusClassEnum: int implements Enumerator
{
    use HasEnumeratorBehavior;
    use HasOrder;

    #[Label('Informational'), Color('info'),    Order(1)] case Informational = 1;
    #[Label('Success'),       Color('success'), Order(2)] case Success       = 2;
    #[Label('Redirection'),   Color('warning'), Order(3)] case Redirection   = 3;
    #[Label('Client Error'),  Color('danger'),  Order(4)] case ClientError   = 4;
    #[Label('Server Error'),  Color('danger'),  Order(5)] case ServerError   = 5;

    public static function fromStatus(int $status): self
    {
        return self::from(intdiv($status, 100));
    }

    public function contains(int $status): bool
    {
        return intdiv($status, 100) === $this->value;
    }
}
PHP

# ============================================================================
# Bitmask demos
# ============================================================================

cat > "$DIR/BasicPermissionEnum.php" <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Simtabi\Laranail\Enumerator\Attributes\{Bit, Label};
use Simtabi\Laranail\Enumerator\Concerns\{HasBitmask, HasEnumeratorBehavior};
use Simtabi\Laranail\Enumerator\Contracts\{Bitwise, Enumerator};

enum BasicPermissionEnum: int implements Enumerator, Bitwise
{
    use HasEnumeratorBehavior;
    use HasBitmask;

    #[Bit(1), Label('Read')]   case Read   = 1;
    #[Bit(2), Label('Write')]  case Write  = 2;
    #[Bit(4), Label('Delete')] case Delete = 4;
    #[Bit(8), Label('Admin')]  case Admin  = 8;
}
PHP

cat > "$DIR/FeatureFlagEnum.php" <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Simtabi\Laranail\Enumerator\Attributes\{Bit, Label};
use Simtabi\Laranail\Enumerator\Concerns\{HasBitmask, HasEnumeratorBehavior};
use Simtabi\Laranail\Enumerator\Contracts\{Bitwise, Enumerator};

enum FeatureFlagEnum: string implements Enumerator, Bitwise
{
    use HasEnumeratorBehavior;
    use HasBitmask;

    #[Bit(1),  Label('Dark Mode')]   case DarkMode    = 'dark_mode';
    #[Bit(2),  Label('Beta UI')]     case BetaUI      = 'beta_ui';
    #[Bit(4),  Label('Experiments')] case Experiments = 'experiments';
    #[Bit(8),  Label('Telemetry')]   case Telemetry   = 'telemetry';
}
PHP

cat > "$DIR/NotificationOptInEnum.php" <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Simtabi\Laranail\Enumerator\Attributes\{Bit, Label};
use Simtabi\Laranail\Enumerator\Concerns\{HasBitmask, HasEnumeratorBehavior};
use Simtabi\Laranail\Enumerator\Contracts\{Bitwise, Enumerator};

enum NotificationOptInEnum implements Enumerator, Bitwise
{
    use HasEnumeratorBehavior;
    use HasBitmask;

    #[Bit(1), Label('Email')]   case Email;
    #[Bit(2), Label('SMS')]     case SMS;
    #[Bit(4), Label('Push')]    case Push;
    #[Bit(8), Label('Webhook')] case Webhook;
}
PHP

# ============================================================================
# Sensitive / Demographic (neutral, no editorial framing)
# ============================================================================

cat > "$DIR/GenderEnum.php" <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumeratorBehavior;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

enum GenderEnum: string implements Enumerator
{
    use HasEnumeratorBehavior;

    #[Label('Female')]            case Female           = 'female';
    #[Label('Male')]              case Male             = 'male';
    #[Label('Non-binary')]        case NonBinary        = 'non_binary';
    #[Label('Other')]             case Other            = 'other';
    #[Label('Prefer not to say')] case PreferNotToSay   = 'prefer_not_to_say';
}
PHP

cat > "$DIR/MaritalStatusEnum.php" <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumeratorBehavior;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

enum MaritalStatusEnum: string implements Enumerator
{
    use HasEnumeratorBehavior;

    #[Label('Single')]                case Single              = 'single';
    #[Label('Married')]               case Married             = 'married';
    #[Label('Divorced')]              case Divorced            = 'divorced';
    #[Label('Widowed')]               case Widowed             = 'widowed';
    #[Label('Separated')]             case Separated           = 'separated';
    #[Label('Domestic Partnership')]  case DomesticPartnership = 'domestic_partnership';
    #[Label('Other')]                 case Other               = 'other';
}
PHP

cat > "$DIR/RaceEnum.php" <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumeratorBehavior;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

enum RaceEnum: string implements Enumerator
{
    use HasEnumeratorBehavior;

    #[Label('American Indian or Alaska Native')]            case AmericanIndianOrAlaskaNative           = 'american_indian_or_alaska_native';
    #[Label('Asian')]                                       case Asian                                  = 'asian';
    #[Label('Black or African American')]                   case BlackOrAfricanAmerican                 = 'black_or_african_american';
    #[Label('Hispanic or Latino')]                          case HispanicOrLatino                       = 'hispanic_or_latino';
    #[Label('Native Hawaiian or Other Pacific Islander')]   case NativeHawaiianOrOtherPacificIslander   = 'native_hawaiian_or_other_pacific_islander';
    #[Label('White')]                                       case White                                  = 'white';
    #[Label('Two or More Races')]                           case TwoOrMoreRaces                         = 'two_or_more_races';
    #[Label('Other')]                                       case Other                                  = 'other';
    #[Label('Prefer not to say')]                           case PreferNotToSay                         = 'prefer_not_to_say';
}
PHP

cat > "$DIR/ReligionEnum.php" <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumeratorBehavior;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

enum ReligionEnum: string implements Enumerator
{
    use HasEnumeratorBehavior;

    #[Label('Christianity')]      case Christianity     = 'christianity';
    #[Label('Islam')]             case Islam            = 'islam';
    #[Label('Hinduism')]          case Hinduism         = 'hinduism';
    #[Label('Buddhism')]          case Buddhism         = 'buddhism';
    #[Label('Judaism')]           case Judaism          = 'judaism';
    #[Label('Sikhism')]           case Sikhism          = 'sikhism';
    #[Label('Folk Religion')]     case FolkReligion     = 'folk_religion';
    #[Label('No Religion')]       case NoReligion       = 'no_religion';
    #[Label('Other')]             case Other            = 'other';
    #[Label('Prefer not to say')] case PreferNotToSay   = 'prefer_not_to_say';
}
PHP

# ============================================================================
# Calendar
# ============================================================================

cat > "$DIR/WeekdayEnum.php" <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use InvalidArgumentException;
use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Concerns\{HasEnumeratorBehavior, HasLifecycle, HasOrder};
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

/**
 * Days of the week. Sunday-first by default (US convention); ISO-8601 and
 * Carbon helpers provided. Hydrate from any convention via fromNumber().
 */
enum WeekdayEnum: int implements Enumerator
{
    use HasEnumeratorBehavior;
    use HasLifecycle;
    use HasOrder;

    #[Label('Sunday')]    case Sunday    = 1;
    #[Label('Monday')]    case Monday    = 2;
    #[Label('Tuesday')]   case Tuesday   = 3;
    #[Label('Wednesday')] case Wednesday = 4;
    #[Label('Thursday')]  case Thursday  = 5;
    #[Label('Friday')]    case Friday    = 6;
    #[Label('Saturday')]  case Saturday  = 7;

    /** Sunday-first: Sun=1..Sat=7. */
    public function number(): int { return $this->value; }

    /** ISO-8601: Mon=1..Sun=7. */
    public function isoNumber(): int
    {
        return $this === self::Sunday ? 7 : $this->value - 1;
    }

    /** Carbon zero-based: Sun=0..Sat=6. */
    public function carbonIndex(): int { return $this->value - 1; }

    public function isWeekend(): bool { return in_array($this, [self::Sunday, self::Saturday], true); }

    public function isWeekday(): bool { return ! $this->isWeekend(); }

    public static function fromNumber(int $n, string $convention = 'sunday-first'): self
    {
        return match ($convention) {
            'sunday-first' => self::from($n),
            'iso-8601'     => $n === 7 ? self::Sunday : self::from($n + 1),
            'carbon'       => self::from($n + 1),
            default        => throw new InvalidArgumentException(sprintf('Unknown convention "%s".', $convention)),
        };
    }
}
PHP

cat > "$DIR/MonthEnum.php" <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Concerns\{HasEnumeratorBehavior, HasLifecycle, HasOrder};
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

enum MonthEnum: int implements Enumerator
{
    use HasEnumeratorBehavior;
    use HasLifecycle;
    use HasOrder;

    #[Label('January')]   case January   = 1;
    #[Label('February')]  case February  = 2;
    #[Label('March')]     case March     = 3;
    #[Label('April')]     case April     = 4;
    #[Label('May')]       case May       = 5;
    #[Label('June')]      case June      = 6;
    #[Label('July')]      case July      = 7;
    #[Label('August')]    case August    = 8;
    #[Label('September')] case September = 9;
    #[Label('October')]   case October   = 10;
    #[Label('November')]  case November  = 11;
    #[Label('December')]  case December  = 12;
}
PHP

# ============================================================================
# MIME
# ============================================================================

cat > "$DIR/MimeTypeCategoryEnum.php" <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumeratorBehavior;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

enum MimeTypeCategoryEnum: string implements Enumerator
{
    use HasEnumeratorBehavior;

    #[Label('Text')]        case Text        = 'text';
    #[Label('Image')]       case Image       = 'image';
    #[Label('Video')]       case Video       = 'video';
    #[Label('Audio')]       case Audio       = 'audio';
    #[Label('Application')] case Application = 'application';
    #[Label('Font')]        case Font        = 'font';
    #[Label('Multipart')]   case Multipart   = 'multipart';
    #[Label('Message')]     case Message     = 'message';
    #[Label('Other')]       case Other       = 'other';

    public static function fromMime(string $mime): self
    {
        $primary = strtolower(explode('/', $mime, 2)[0] ?? '');

        return match ($primary) {
            'text'        => self::Text,
            'image'       => self::Image,
            'video'       => self::Video,
            'audio'       => self::Audio,
            'application' => self::Application,
            'font'        => self::Font,
            'multipart'   => self::Multipart,
            'message'     => self::Message,
            default       => self::Other,
        };
    }
}
PHP

# ============================================================================
# Role flags (string-backed bitmask)
# ============================================================================

cat > "$DIR/RoleFlagEnum.php" <<'PHP'
<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Simtabi\Laranail\Enumerator\Attributes\Bit;
use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Concerns\HasBitmask;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumeratorBehavior;
use Simtabi\Laranail\Enumerator\Contracts\Bitwise;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

/**
 * Role-based access flags as a bitmask. Combine roles via `RoleFlagEnum::mask(...)`
 * and store the int via `Casts\AsBitmask::of(RoleFlagEnum::class)`.
 *
 * Migrated from the legacy class-const path (`AbstractEnumeratorClass`) to a
 * native PHP 8.3+ enum so all framework features (`HasBitmask`, native
 * `cases()`, `tryFrom()`) work out of the box.
 */
enum RoleFlagEnum: string implements Bitwise, Enumerator
{
    use HasBitmask;
    use HasEnumeratorBehavior;

    #[Bit(1), Label('Subscriber')]
    case Subscriber = 'subscriber';

    #[Bit(2), Label('Contributor')]
    case Contributor = 'contributor';

    #[Bit(4), Label('Editor')]
    case Editor = 'editor';

    #[Bit(8), Label('Admin')]
    case Admin = 'admin';
}
PHP

echo "==> 26 preset enums written"
ls "$DIR" | wc -l | xargs -I N echo "Presets/Enums count: N"
