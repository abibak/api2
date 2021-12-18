<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CreateTriggerFunctions extends Migration
{
    /**up
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared(
            "
CREATE OR REPLACE FUNCTION trigger_min_max()
    RETURNS TRIGGER AS
$$
BEGIN
    IF NEW.urgency >= 1 AND NEW.urgency <= 5 THEN
        RETURN NEW;
    ELSE
        RAISE 'От 1 до 5 включительно';
    END IF;
END;
$$ LANGUAGE plpgsql;



CREATE OR REPLACE FUNCTION trigger_count_tasks()
    RETURNS TRIGGER AS
$$
BEGIN
    IF TG_OP = 'INSERT' THEN
        UPDATE public.lists
        SET count_tasks = (SELECT COUNT(*) FROM public.tasks WHERE list_id = NEW.list_id)
        WHERE id = NEW.list_id;
        RETURN NEW;
    ELSEIF TG_OP = 'DELETE' THEN
        UPDATE public.lists
        SET count_tasks = (SELECT COUNT(*) FROM public.tasks WHERE list_id = OLD.list_id)
        WHERE id = OLD.list_id;
        RETURN OLD;
    ELSEIF TG_OP = 'UPDATE' THEN
        UPDATE public.lists
        SET count_tasks = (SELECT COUNT(*) FROM public.tasks WHERE list_id = OLD.list_id)
        WHERE id = OLD.list_id;
        UPDATE public.lists
        SET count_tasks = (SELECT COUNT(*) FROM public.tasks WHERE list_id = NEW.list_id)
        WHERE id = NEW.list_id;
        RETURN NEW;
    END IF;
END;
$$ LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION trigger_is_completed()
    RETURNS TRIGGER AS
$$
BEGIN
    IF TG_OP = 'DELETE' THEN
        IF (SELECT count_tasks FROM public.lists WHERE id = OLD.list_id) > 0 AND
           (SELECT COUNT(*) FROM public.tasks WHERE list_id = OLD.list_id AND is_completed = TRUE) =
           (SELECT count_tasks FROM public.lists WHERE id = OLD.list_id) THEN
            UPDATE public.lists SET is_completed = TRUE WHERE id = OLD.list_id;
            RETURN OLD;
        ELSE
            UPDATE public.lists SET is_completed = FALSE WHERE id = OLD.list_id;
            RETURN OLD;
        END IF;
    ELSE
        IF (SELECT count_tasks FROM public.lists WHERE id = NEW.list_id) > 0 AND
           (SELECT COUNT(*) FROM public.tasks WHERE list_id = NEW.list_id AND is_completed = TRUE) =
           (SELECT count_tasks FROM public.lists WHERE id = NEW.list_id) THEN
            UPDATE public.lists SET is_completed = TRUE WHERE id = NEW.list_id;
            RETURN OLD;
        ELSE
            UPDATE public.lists SET is_completed = FALSE WHERE id = NEW.list_id;
            RETURN OLD;
        END IF;
    END IF;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION trigger_count_tasks_create()
    RETURNS TRIGGER AS
$$
BEGIN
    NEW.count_tasks = (SELECT COUNT(list_id) FROM public.tasks WHERE list_id = NEW.id);
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION trigger_is_completed_create()
    RETURNS TRIGGER AS
$$
BEGIN
    IF (SELECT COUNT(*) FROM public.tasks WHERE list_id = NEW.id AND is_completed = false) >= 0 THEN
        NEW.is_completed = FALSE;
        RETURN NEW;
    END IF;
END;
$$ LANGUAGE plpgsql;


CREATE TRIGGER min_max
BEFORE INSERT OR UPDATE ON public.tasks
FOR EACH ROW EXECUTE FUNCTION trigger_min_max();

CREATE TRIGGER count_tasks
AFTER INSERT OR DELETE OR UPDATE ON public.tasks
FOR EACH ROW EXECUTE FUNCTION trigger_count_tasks();

CREATE TRIGGER is_completed
AFTER INSERT OR DELETE OR UPDATE ON public.tasks
FOR EACH ROW EXECUTE FUNCTION trigger_is_completed();
"
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('
            DROP FUNCTION trigger_min_max CASCADE;
            DROP FUNCTION trigger_count_tasks CASCADE;
            DROP FUNCTION trigger_is_completed CASCADE;
        ');
    }
}
