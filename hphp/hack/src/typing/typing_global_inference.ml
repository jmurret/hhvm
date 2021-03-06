(*
 * Copyright (c) Facebook, Inc. and its affiliates.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the "hack" directory of this source tree.
 *
 *)
open Hh_prelude
open Typing_defs
open Typing_env_types
module Inf = Typing_inference_env
module Env = Typing_env
module Sub = Typing_subtype
module MakeType = Typing_make_type

module StateErrors = struct
  module IdentMap = WrappedMap.Make (Ident)

  type t = Errors.error list IdentMap.t ref

  let mk_empty () = ref IdentMap.empty

  let get_errors t id = Option.value (IdentMap.find_opt id !t) ~default:[]

  let add t id err = t := IdentMap.add id (err :: get_errors t id) !t

  let has_error t id = not @@ List.is_empty @@ get_errors t id

  let elements t = IdentMap.elements !t

  let cardinal t = IdentMap.fold (fun _ l acc -> acc + List.length l) !t 0
end

let make_error_callback errors var ?code msgl =
  let code =
    Option.value code ~default:Error_codes.Typing.(err_code UnifyError)
  in
  StateErrors.add errors var (Errors.make_error code msgl)

let catch_exc pos (on_error : Errors.typing_error_callback) r f =
  try
    let (other_errors, v) =
      Errors.do_with_context (Pos.filename pos) Errors.Typing (fun () ->
          f on_error)
    in
    List.iter (Errors.get_error_list other_errors) ~f:(fun error ->
        on_error (Errors.to_list error));
    v
  with e ->
    let e = Printf.sprintf "Exception: %s" (Exn.to_string e) in
    on_error [(pos, e)];
    r

module type MarshalledData = sig
  type t
end

module StateFunctor (M : MarshalledData) = struct
  type t = M.t

  let load path =
    let channel = In_channel.create path in
    let data : t = Marshal.from_channel channel in
    In_channel.close channel;
    data

  let save path t =
    let out_channel = Out_channel.create path in
    Marshal.to_channel out_channel t [];
    Out_channel.close out_channel
end

let artifacts_path : string ref = ref ""

module StateConstraintGraph = struct
  include StateFunctor (struct
    type t = env * StateErrors.t
  end)

  let merge_var (env, errors) (pos, subgraph) var =
    let catch_exc = catch_exc pos in
    let (env, var') =
      Env.copy_tyvar_from_genv_to_env var ~from:subgraph ~to_:env
    in
    let ty = MakeType.tyvar Reason.Rnone var
    and ty' = MakeType.tyvar Reason.Rnone var' in
    let on_err = make_error_callback errors var in
    let env = catch_exc on_err env (Sub.sub_type env ty ty') in
    let env = catch_exc on_err env (Sub.sub_type env ty' ty) in
    let env =
      catch_exc on_err env (fun _ ->
          Typing_solver.try_bind_to_equal_bound env var')
    in
    let env =
      catch_exc on_err env (fun _ ->
          Typing_solver.try_bind_to_equal_bound env var)
    in
    (env, errors)

  let merge_subgraph (env, errors) ((pos, subgraph) : Inf.t_global_with_pos) =
    let vars = Inf.get_vars_g subgraph in
    List.fold vars ~init:(env, errors) ~f:(fun (env, errors) var ->
        merge_var (env, errors) (pos, subgraph) var)

  let merge_subgraphs (env, errors) subgraphs =
    List.fold ~f:merge_subgraph ~init:(env, errors) subgraphs
end

module StateSubConstraintGraphs = struct
  include StateFunctor (struct
    type t = Inf.t_global_with_pos list
  end)

  let save_to = save

  let save subconstraints =
    let subconstraints =
      List.map subconstraints ~f:(fun (p, env) -> (p, Inf.compress_g env))
    in
    let is_not_empty_graph (_p, g) = not @@ List.is_empty (Inf.get_vars_g g) in
    let subconstraints = List.filter ~f:is_not_empty_graph subconstraints in
    if List.is_empty subconstraints then
      ()
    else
      let path =
        Filename.concat
          !artifacts_path
          ("subgraph_" ^ string_of_int (Ident.tmp ()))
      in
      save path subconstraints
end

module StateSolvedGraph = struct
  include StateFunctor (struct
    type t = env * StateErrors.t * (Pos.t * Ident.t) list
  end)

  let save path t = save path t

  (** Solve the constraint graph. *)
  let from_constraint_graph (env, errors) =
    let vars = Env.get_all_tyvars env in
    let positions =
      List.map vars ~f:(fun var -> (Env.get_tyvar_pos env var, var))
    in

    (* For any errors seen during the last step (that is graph merging), we bind
    the corresponding tyvar to Terr *)
    let env =
      List.fold vars ~init:env ~f:(fun env var ->
          if StateErrors.has_error errors var then
            Typing_solver.bind
              env
              var
              (Typing_defs.mk (Typing_reason.Rnone, Typing_defs.Terr))
          else
            env)
    in
    let env =
      catch_exc Pos.none (make_error_callback errors 0) env @@ fun _ ->
      Typing_solver.solve_all_unsolved_tyvars_gi
        env
        (make_error_callback errors)
    in
    (env, errors, positions)
end

let set_path () =
  let tmp = Tmp.temp_dir GlobalConfig.tmp_dir "gi_artifacts" in
  artifacts_path := tmp

let get_path () = !artifacts_path

let restore_path s = artifacts_path := s

let init () =
  let path = !artifacts_path in
  Hh_logger.log "Global artifacts path: %s" path;
  Disk.rm_dir_tree path;
  if not @@ Disk.file_exists path then Disk.mkdir path 0o777
